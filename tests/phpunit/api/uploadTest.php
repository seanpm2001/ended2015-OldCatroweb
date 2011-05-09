<?php
/*    Catroid: An on-device graphical programming language for Android devices
 *    Copyright (C) 2010-2011 The Catroid Team
 *    (<http://code.google.com/p/catroid/wiki/Credits>)
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once('testsBootstrap.php');

class uploadTest extends PHPUnit_Framework_TestCase
{
  protected $upload;

  protected function setUp() {
    require_once CORE_BASE_PATH.'modules/api/upload.php';
    $this->upload = new upload();
  }

  /**
   * @dataProvider correctPostData
   */
  public function testDoUpload($projectTitle, $projectDescription, $testFile, $fileName, $fileChecksum, $fileSize, $fileType, $uploadImei = '', $uploadEmail = '', $uploadLanguage = '') {
    $formData = array('projectTitle'=>$projectTitle, 'projectDescription'=>$projectDescription, 'fileChecksum'=>$fileChecksum, 'deviceIMEI'=>$uploadImei, 'userEmail'=>$uploadEmail, 'userLanguage'=>$uploadLanguage);
    $fileData = array('upload'=>array('name'=>$fileName, 'type'=>$fileType, 'tmp_name'=>$testFile, 'error'=>0, 'size'=>$fileSize));
    $serverData = array('REMOTE_ADDR'=>'127.0.0.1');
    $fileSize = filesize($testFile);
    $insertId = $this->upload->doUpload($formData, $fileData, $serverData);
    $filePath = CORE_BASE_PATH.PROJECTS_DIRECTORY.$insertId.PROJECTS_EXTENTION;

    $this->assertEquals(200, $this->upload->statusCode);
    $this->assertNotEquals(0, $insertId);
    $this->assertTrue(is_file($filePath));
    $this->assertTrue($this->upload->projectId > 0);
    $this->assertTrue($this->upload->fileChecksum != null);
    $this->assertEquals(md5_file($testFile), $this->upload->fileChecksum);
    $this->assertTrue(is_string($this->upload->answer));


    if($uploadImei) {
      $query = "SELECT upload_imei FROM projects WHERE id='$insertId'";
      $result = pg_query($query);
      $row = pg_fetch_row($result);
      $this->assertEquals($uploadImei, $row[0]);
      pg_free_result($result);
    }
    if($uploadEmail) {
      $query = "SELECT upload_email FROM projects WHERE id='$insertId'";
      $result = pg_query($query);
      $row = pg_fetch_row($result);
      $this->assertEquals($uploadEmail, $row[0]);
      pg_free_result($result);
    }
    if($uploadLanguage) {
      $query = "SELECT upload_language FROM projects WHERE id='$insertId'";
      $result = pg_query($query);
      $row = pg_fetch_row($result);
      $this->assertEquals($uploadLanguage, $row[0]);
      pg_free_result($result);
    }
    if($fileSize) {
      $query = "SELECT filesize_bytes FROM projects WHERE id='$insertId'";
      $result = pg_query($query);
      $row = pg_fetch_row($result);
      $this->assertEquals($fileSize, $row[0]);
      pg_free_result($result);
    }

    //test qrcode image generation
    $this->assertTrue(is_file(CORE_BASE_PATH.PROJECTS_QR_DIRECTORY.$insertId.PROJECTS_QR_EXTENTION));

    //test renaming
    $return = $this->upload->renameProjectFile($filePath, $insertId);
    $this->assertTrue($return);

    //test deleting from filesystem
    $this->upload->removeProjectFromFilesystem($filePath, $insertId);
    $this->assertFalse(is_file($filePath));
    $this->assertFalse(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_SMALL));
    $this->assertFalse(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_LARGE));
    $this->assertFalse(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_ORIG));
    @unlink(CORE_BASE_PATH.PROJECTS_QR_DIRECTORY.$insertId.PROJECTS_QR_EXTENTION);

    //test deleting from database
    $this->upload->removeProjectFromDatabase($insertId);
    $query = "SELECT * FROM projects WHERE id='$insertId'";
    $result = pg_query($query) or die('DB operation failed: ' . pg_last_error());
    $this->assertEquals(0, pg_num_rows($result));
  }

  /**
   * @dataProvider incorrectPostData
   */
  public function testDoUploadFail($projectTitle, $projectDescription, $testFile, $fileName, $fileChecksum, $fileSize, $fileType, $expectedStatusCode, $uploadImei = '', $uploadEmail = '', $uploadLanguage = '') {
    $formData = array('projectTitle'=>$projectTitle, 'projectDescription'=>$projectDescription, 'fileChecksum'=>$fileChecksum, 'deviceIMEI'=>$uploadImei, 'userEmail'=>$uploadEmail, 'userLanguage'=>$uploadLanguage);
    $fileData = array('upload'=>array('name'=>$fileName, 'type'=>$fileType, 'tmp_name'=>$testFile, 'error'=>0, 'size'=>$fileSize));
    $serverData = array('REMOTE_ADDR'=>'127.0.0.1');
    $insertId = $this->upload->doUpload($formData, $fileData, $serverData);
    $filePath = CORE_BASE_PATH.PROJECTS_DIRECTORY.$insertId.PROJECTS_EXTENTION;

    $this->assertNotEquals(200, $this->upload->statusCode);
    $this->assertEquals($expectedStatusCode, $this->upload->statusCode);
    $this->assertEquals(0, $insertId);
    $this->assertFalse(is_file($filePath));

    $this->assertFalse(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_SMALL));
    $this->assertFalse(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_LARGE));
    $this->assertFalse(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_ORIG));
    $this->assertNotEquals(200, $this->upload->statusCode);
    $this->assertFalse($this->upload->projectId > 0);
    $this->assertTrue(is_string($this->upload->answer));
  }

  public function testCheckFileChecksum() {
    $csOne = '12abc';
    $csTwo = '12abc';
    $this->assertTrue($this->upload->checkFileChecksum($csOne, $csTwo));
    $csOne = '12abc';
    $csTwo = '21cba';
    $this->assertFalse($this->upload->checkFileChecksum($csOne, $csTwo));
  }

  public function testCopyProjectToDirectory() {
    $dest = CORE_BASE_PATH.PROJECTS_DIRECTORY.'copyTest'.PROJECTS_EXTENTION;
    $src = dirname(__FILE__).'/testdata/test.zip';
    @unlink($dest);
    $this->assertEquals(filesize($src), $this->upload->copyProjectToDirectory($src, $dest));
    $this->assertTrue(is_file($dest));
    @unlink($dest);
  }

  public function testCopyProjectWithThumbnailToDirectory() {
    $dest = CORE_BASE_PATH.PROJECTS_DIRECTORY.'copyTest'.PROJECTS_EXTENTION;
    $src = dirname(__FILE__).'/testdata/test2.zip';
    @unlink($dest);
    $this->assertEquals(filesize($src), $this->upload->copyProjectToDirectory($src, $dest));
    $this->assertTrue(is_file($dest));
    @unlink($dest);
  }

  /**
   * @dataProvider correctPostDataThumbailInRootFolderJPG
   */
  public function testDoUploadWithThumbnailInRootFolderJPG($projectTitle, $projectDescription, $testFile, $fileName, $fileChecksum, $fileSize, $fileType, $uploadImei = '', $uploadEmail = '', $uploadLanguage = '') {
    $formData = array('projectTitle'=>$projectTitle, 'projectDescription'=>$projectDescription, 'fileChecksum'=>$fileChecksum, 'deviceIMEI'=>$uploadImei, 'userEmail'=>$uploadEmail, 'userLanguage'=>$uploadLanguage);
    $fileData = array('upload'=>array('name'=>$fileName, 'type'=>$fileType, 'tmp_name'=>$testFile, 'error'=>0, 'size'=>$fileSize));
    $serverData = array('REMOTE_ADDR'=>'127.0.0.1');
    $insertId = $this->upload->doUpload($formData, $fileData, $serverData);
    $filePath = CORE_BASE_PATH.PROJECTS_DIRECTORY.$insertId.PROJECTS_EXTENTION;

    $this->assertEquals(200, $this->upload->statusCode);
    $this->assertNotEquals(0, $insertId);
    $this->assertTrue(is_file($filePath));
    $this->assertTrue($this->upload->projectId > 0);
    $this->assertTrue($this->upload->fileChecksum != null);
    $this->assertEquals(md5_file($testFile), $this->upload->fileChecksum);
    $this->assertTrue(is_string($this->upload->answer));

    // check thumbnails
    $this->assertTrue(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_SMALL));
    $this->assertTrue(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_LARGE));
    $this->assertTrue(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_ORIG));

    //test deleting from filesystem
    $this->upload->removeProjectFromFilesystem($filePath, $insertId);
    $this->assertFalse(is_file($filePath));
    $this->assertFalse(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_SMALL));
    $this->assertFalse(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_LARGE));
    $this->assertFalse(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_ORIG));
    @unlink(CORE_BASE_PATH.PROJECTS_QR_DIRECTORY.$insertId.PROJECTS_QR_EXTENTION);

    //test deleting from database
    $this->upload->removeProjectFromDatabase($insertId);
    $query = "SELECT * FROM projects WHERE id='$insertId'";
    $result = pg_query($query) or die('DB operation failed: ' . pg_last_error());
    $this->assertEquals(0, pg_num_rows($result));
  }

  /**
   * @dataProvider correctPostDataThumbailInImagesFolderJPG
   */
  public function testDoUploadWithThumbnailInImagesFolderJPG($projectTitle, $projectDescription, $testFile, $fileName, $fileChecksum, $fileSize, $fileType, $uploadImei = '', $uploadEmail = '', $uploadLanguage = '') {
    $formData = array('projectTitle'=>$projectTitle, 'projectDescription'=>$projectDescription, 'fileChecksum'=>$fileChecksum, 'deviceIMEI'=>$uploadImei, 'userEmail'=>$uploadEmail, 'userLanguage'=>$uploadLanguage);
    $fileData = array('upload'=>array('name'=>$fileName, 'type'=>$fileType, 'tmp_name'=>$testFile, 'error'=>0, 'size'=>$fileSize));
    $serverData = array('REMOTE_ADDR'=>'127.0.0.1');
    $insertId = $this->upload->doUpload($formData, $fileData, $serverData);
    $filePath = CORE_BASE_PATH.PROJECTS_DIRECTORY.$insertId.PROJECTS_EXTENTION;

    $this->assertEquals(200, $this->upload->statusCode);
    $this->assertNotEquals(0, $insertId);
    $this->assertTrue(is_file($filePath));
    $this->assertTrue($this->upload->projectId > 0);
    $this->assertTrue($this->upload->fileChecksum != null);
    $this->assertEquals(md5_file($testFile), $this->upload->fileChecksum);
    $this->assertTrue(is_string($this->upload->answer));

    // check thumbnails
    $this->assertTrue(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_SMALL));
    $this->assertTrue(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_LARGE));
    $this->assertTrue(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_ORIG));

    //test deleting from filesystem
    $this->upload->removeProjectFromFilesystem($filePath, $insertId);
    $this->assertFalse(is_file($filePath));
    $this->assertFalse(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_SMALL));
    $this->assertFalse(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_LARGE));
    $this->assertFalse(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_ORIG));
    @unlink(CORE_BASE_PATH.PROJECTS_QR_DIRECTORY.$insertId.PROJECTS_QR_EXTENTION);

    //test deleting from database
    $this->upload->removeProjectFromDatabase($insertId);
    $query = "SELECT * FROM projects WHERE id='$insertId'";
    $result = pg_query($query) or die('DB operation failed: ' . pg_last_error());
    $this->assertEquals(0, pg_num_rows($result));
  }

  /**
   * @dataProvider correctPostDataThumbailInRootFolderPNG
   */
  public function testDoUploadWithThumbnailInRootFolderPNG($projectTitle, $projectDescription, $testFile, $fileName, $fileChecksum, $fileSize, $fileType, $uploadImei = '', $uploadEmail = '', $uploadLanguage = '') {
    $formData = array('projectTitle'=>$projectTitle, 'projectDescription'=>$projectDescription, 'fileChecksum'=>$fileChecksum, 'deviceIMEI'=>$uploadImei, 'userEmail'=>$uploadEmail, 'userLanguage'=>$uploadLanguage);
    $fileData = array('upload'=>array('name'=>$fileName, 'type'=>$fileType, 'tmp_name'=>$testFile, 'error'=>0, 'size'=>$fileSize));
    $serverData = array('REMOTE_ADDR'=>'127.0.0.1');
    $insertId = $this->upload->doUpload($formData, $fileData, $serverData);
    $filePath = CORE_BASE_PATH.PROJECTS_DIRECTORY.$insertId.PROJECTS_EXTENTION;

    $this->assertEquals(200, $this->upload->statusCode);
    $this->assertNotEquals(0, $insertId);
    $this->assertTrue(is_file($filePath));
    $this->assertTrue($this->upload->projectId > 0);
    $this->assertTrue($this->upload->fileChecksum != null);
    $this->assertEquals(md5_file($testFile), $this->upload->fileChecksum);
    $this->assertTrue(is_string($this->upload->answer));

    // check thumbnails
    $this->assertTrue(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_SMALL));
    $this->assertTrue(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_LARGE));
    $this->assertTrue(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_ORIG));

    //test deleting from filesystem
    $this->upload->removeProjectFromFilesystem($filePath, $insertId);
    $this->assertFalse(is_file($filePath));
    $this->assertFalse(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_SMALL));
    $this->assertFalse(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_LARGE));
    $this->assertFalse(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_ORIG));
    @unlink(CORE_BASE_PATH.PROJECTS_QR_DIRECTORY.$insertId.PROJECTS_QR_EXTENTION);

    //test deleting from database
    $this->upload->removeProjectFromDatabase($insertId);
    $query = "SELECT * FROM projects WHERE id='$insertId'";
    $result = pg_query($query) or die('DB operation failed: ' . pg_last_error());
    $this->assertEquals(0, pg_num_rows($result));
  }

  /**
   * @dataProvider correctPostDataThumbailInImagesFolderPNG
   */
  public function testDoUploadWithThumbnailInImagesFolderPNG($projectTitle, $projectDescription, $testFile, $fileName, $fileChecksum, $fileSize, $fileType, $uploadImei = '', $uploadEmail = '', $uploadLanguage = '') {
    $formData = array('projectTitle'=>$projectTitle, 'projectDescription'=>$projectDescription, 'fileChecksum'=>$fileChecksum, 'deviceIMEI'=>$uploadImei, 'userEmail'=>$uploadEmail, 'userLanguage'=>$uploadLanguage);
    $fileData = array('upload'=>array('name'=>$fileName, 'type'=>$fileType, 'tmp_name'=>$testFile, 'error'=>0, 'size'=>$fileSize));
    $serverData = array('REMOTE_ADDR'=>'127.0.0.1');
    $insertId = $this->upload->doUpload($formData, $fileData, $serverData);
    $filePath = CORE_BASE_PATH.PROJECTS_DIRECTORY.$insertId.PROJECTS_EXTENTION;

    $this->assertEquals(200, $this->upload->statusCode);
    $this->assertNotEquals(0, $insertId);
    $this->assertTrue(is_file($filePath));
    $this->assertTrue($this->upload->projectId > 0);
    $this->assertTrue($this->upload->fileChecksum != null);
    $this->assertEquals(md5_file($testFile), $this->upload->fileChecksum);
    $this->assertTrue(is_string($this->upload->answer));

    // check thumbnails
    $this->assertTrue(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_SMALL));
    $this->assertTrue(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_LARGE));
    $this->assertTrue(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_ORIG));

    //test deleting from filesystem
    $this->upload->removeProjectFromFilesystem($filePath, $insertId);
    $this->assertFalse(is_file($filePath));
    $this->assertFalse(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_SMALL));
    $this->assertFalse(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_LARGE));
    $this->assertFalse(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_ORIG));
    @unlink(CORE_BASE_PATH.PROJECTS_QR_DIRECTORY.$insertId.PROJECTS_QR_EXTENTION);

    //test deleting from database
    $this->upload->removeProjectFromDatabase($insertId);
    $query = "SELECT * FROM projects WHERE id='$insertId'";
    $result = pg_query($query) or die('DB operation failed: ' . pg_last_error());
    $this->assertEquals(0, pg_num_rows($result));
  }

  /**
   * @dataProvider incorrectPostDataWithThumbnail
   */
  public function testDoUploadWithThumbnailFail($projectTitle, $projectDescription, $testFile, $fileName, $fileChecksum, $fileSize, $fileType, $uploadImei = '', $uploadEmail = '', $uploadLanguage = '') {
    $formData = array('projectTitle'=>$projectTitle, 'projectDescription'=>$projectDescription, 'fileChecksum'=>$fileChecksum, 'deviceIMEI'=>$uploadImei, 'userEmail'=>$uploadEmail, 'userLanguage'=>$uploadLanguage);
    $fileData = array('upload'=>array('name'=>$fileName, 'type'=>$fileType, 'tmp_name'=>$testFile, 'error'=>0, 'size'=>$fileSize));
    $serverData = array('REMOTE_ADDR'=>'127.0.0.1');
    $insertId = $this->upload->doUpload($formData, $fileData, $serverData);
    $filePath = CORE_BASE_PATH.PROJECTS_DIRECTORY.$insertId.PROJECTS_EXTENTION;

    $this->assertEquals(200, $this->upload->statusCode);
    $this->assertNotEquals(0, $insertId);
    $this->assertTrue(is_file($filePath));
    $this->assertTrue($this->upload->projectId > 0);
    $this->assertTrue($this->upload->fileChecksum != null);
    $this->assertEquals(md5_file($testFile), $this->upload->fileChecksum);
    $this->assertTrue(is_string($this->upload->answer));

    // check thumbnails
    $this->assertFalse(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_SMALL));
    $this->assertFalse(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_LARGE));
    $this->assertFalse(is_file(CORE_BASE_PATH.'/'.PROJECTS_THUMBNAIL_DIRECTORY.'/'.$insertId.PROJECTS_THUMBNAIL_EXTENTION_ORIG));

    //test deleting from filesystem
    $this->upload->removeProjectFromFilesystem($filePath, $insertId);
    $this->assertFalse(is_file($filePath));
    @unlink(CORE_BASE_PATH.PROJECTS_QR_DIRECTORY.$insertId.PROJECTS_QR_EXTENTION);

    //test deleting from database
    $this->upload->removeProjectFromDatabase($insertId);
    $query = "SELECT * FROM projects WHERE id='$insertId'";
    $result = pg_query($query) or die('DB operation failed: ' . pg_last_error());
    $this->assertEquals(0, pg_num_rows($result));
  }

  /* *** DATA PROVIDERS *** */
  public function correctPostData() {
    $fileName = 'test.zip';
    $fileNameWithThumbnail = 'test2.zip';
    $testFile = dirname(__FILE__).'/testdata/'.$fileName;
    $testFileWithThumbnail = dirname(__FILE__).'/testdata/'.$fileNameWithThumbnail;
    $fileChecksum = md5_file($testFile);
    $fileChecksumWithThumbnail = md5_file($testFileWithThumbnail);
    $fileSize = filesize($testFile);
    $fileSizeWithThumbnail = filesize($testFileWithThumbnail);
    $fileType = 'application/x-zip-compressed';
    $dataArray = array(
    array('unitTest', 'my project description', $testFile, $fileName, $fileChecksum, $fileSize, $fileType),
    array('unitTest with empty description', '', $testFile, $fileName, $fileChecksum, $fileSize, $fileType),
    array('unitTest with a very very very very long title and no description, hopefully not too long', 'description', $testFile, $fileName, $fileChecksum, $fileSize, $fileType),
    array('unitTest with special chars: ä, ü, ö', 'jüßt 4 spècia1 char **test** %&()[]{}_|~#', $testFile, $fileName, $fileChecksum, $fileSize, $fileType),
    array('unitTest with included Thumbnail', 'this project contains its thumbnail inside the zip file', $testFileWithThumbnail, $fileNameWithThumbnail, $fileChecksumWithThumbnail, $fileSizeWithThumbnail, $fileType),
    array('unitTest with long description', 'this is a long description. this is a long description. this is a long description. this is a long description. this is a long description. this is a long description. this is a long description. this is a long description. this is a long description. this is a long description.', $testFile, $fileName, $fileChecksum, $fileSize, $fileType),
    array('unitTest with IMEI, Email and Language', 'description', $testFile, $fileName, $fileChecksum, $fileSize, $fileType, '12345rtgfb67854', 'catroid_unittest@gmail.com', 'en'),
    array('unitTest', 'my project description with thumbnail in root folder.', $testFile, 'test2.zip', $fileChecksum, $fileSize, $fileType),
    array('unitTest', 'my project description with thumbnail in images folder.', $testFile, 'test3.zip', $fileChecksum, $fileSize, $fileType),
    );
    return $dataArray;
  }

  public function incorrectPostData() {
    $validFileName = 'test.zip';
    $invalidFileName = 'nonExistingFile.zip';
    $validTestFile = dirname(__FILE__).'/testdata/'.$validFileName;
    $invalidTestFile = dirname(__FILE__).'/testdata/'.$invalidFileName;
    $validFileChecksum = md5_file($validTestFile);
    $invalidFileChecksum = 'invalidfilechecksum';
    $validFileSize = filesize($validTestFile);
    $fileType = 'application/x-zip-compressed';
    $dataArray = array(
    array('unitTestFail1', 'this project uses a non existing file for upload', $invalidTestFile, $invalidFileName, $validFileChecksum, 0, $fileType, 504),
    array('unitTestFail9', 'no file checksum is send together with this project', $validTestFile, $validFileName, '', $validFileSize, $fileType, 510),
    array('', 'this project has an empty projectTitle', $validTestFile, $validFileName, $validFileChecksum, $validFileSize, $fileType, 509),
    array(PROJECT_DEFAULT_SAVEFILE_NAME, 'this project is named defaultProject', $validTestFile, $validFileName, $validFileChecksum, $validFileSize, $fileType, 507),
    array('unitTestFail2', 'this project has an invalid fileChecksum', $validTestFile, $validFileName, $invalidFileChecksum, $validFileSize, $fileType, 501),
    array('unitTestFail3', 'this project has a too large project file', $validTestFile, $validFileName, $validFileChecksum, 200000000, $fileType, 508),
    array(PROJECT_DEFAULT_SAVEFILE_NAME, 'this project has the default save file set.', $validTestFile, $validFileName, $validFileChecksum, $validFileSize, $fileType, 507),
    array('my fucking project title', 'this project has an insulting projectTitle', $validTestFile, $validFileName, $validFileChecksum, $validFileSize, $fileType, 506),
    array('insulting description', 'this project has an insulting projectDescription - Fuck!', $validTestFile, $validFileName, $validFileChecksum, $validFileSize, $fileType, 505)
    );
    return $dataArray;
  }

  public function correctPostDataThumbailInRootFolderJPG() {
    $fileName = 'test_thumbnail_jpg.zip';
    $testFile = dirname(__FILE__).'/testdata/'.$fileName;
    $testFileDir = dirname(__FILE__).'/testdata/';
    $fileChecksum = md5_file($testFile);
    $fileSize = filesize($testFile);
    $fileType = 'application/x-zip-compressed';

    $testFile1 = 'test_thumbnail_240x400.zip'; $testFileDir1 = $testFileDir.'test_thumbnail_240x400.zip';
    $testFile2 = 'test_thumbnail_480x800.zip'; $testFileDir2 = $testFileDir.'test_thumbnail_480x800.zip';
    $testFile3 = 'test_thumbnail_240x240.zip'; $testFileDir3 = $testFileDir.'test_thumbnail_240x240.zip';
    $testFile4 = 'test_thumbnail_480x480.zip'; $testFileDir4 = $testFileDir.'test_thumbnail_480x480.zip';
    $testFile5 = 'test_thumbnail_400x400.zip'; $testFileDir5 = $testFileDir.'test_thumbnail_400x400.zip';
    $testFile6 = 'test_thumbnail_800x800.zip'; $testFileDir6 = $testFileDir.'test_thumbnail_800x800.zip';
    $testFile7 = 'test_thumbnail_960x1600.zip'; $testFileDir7 = $testFileDir.'test_thumbnail_960x1600.zip';
    $testFile8 = 'test_thumbnail_400x240.zip'; $testFileDir8 = $testFileDir.'test_thumbnail_400x240.zip';
    $testFile9 = 'test_thumbnail_800x480.zip'; $testFileDir9 = $testFileDir.'test_thumbnail_800x480.zip';

    $dataArray = array(
    array('unitTest', 'my project description with thumbnail of type JPG in root folder and default thumbnail.', $testFile, $fileName, $fileChecksum, $fileSize, $fileType),
    array('unitTest', 'my project description with thumbnail of type JPG in root folder and thumbnail 240x400.', $testFileDir1, $testFile1, md5_file($testFileDir1), filesize($testFileDir1), $fileType),
    array('unitTest', 'my project description with thumbnail of type JPG in root folder and thumbnail 480x800.', $testFileDir2, $testFile2, md5_file($testFileDir2), filesize($testFileDir2), $fileType),
    array('unitTest', 'my project description with thumbnail of type JPG in root folder and thumbnail 240x240.', $testFileDir3, $testFile3, md5_file($testFileDir3), filesize($testFileDir3), $fileType),
    array('unitTest', 'my project description with thumbnail of type JPG in root folder and thumbnail 480x480.', $testFileDir4, $testFile4, md5_file($testFileDir4), filesize($testFileDir4), $fileType),
    array('unitTest', 'my project description with thumbnail of type JPG in root folder and thumbnail 400x400.', $testFileDir5, $testFile5, md5_file($testFileDir5), filesize($testFileDir5), $fileType),
    array('unitTest', 'my project description with thumbnail of type JPG in root folder and thumbnail 800x800.', $testFileDir6, $testFile6, md5_file($testFileDir6), filesize($testFileDir6), $fileType),
    array('unitTest', 'my project description with thumbnail of type JPG in root folder and thumbnail 960x1600.', $testFileDir7, $testFile7, md5_file($testFileDir7), filesize($testFileDir7), $fileType),
    array('unitTest', 'my project description with thumbnail of type JPG in root folder and thumbnail 400x240.', $testFileDir8, $testFile8, md5_file($testFileDir8), filesize($testFileDir8), $fileType),
    array('unitTest', 'my project description with thumbnail of type JPG in root folder and thumbnail 800x480.', $testFileDir9, $testFile9, md5_file($testFileDir9), filesize($testFileDir9), $fileType)
    );
    return $dataArray;
  }

  public function correctPostDataThumbailInImagesFolderJPG() {
    $fileName = 'test_thumbnail_jpg_in_images.zip';
    $testFile = dirname(__FILE__).'/testdata/'.$fileName;
    $fileChecksum = md5_file($testFile);
    $fileSize = filesize($testFile);
    $fileType = 'application/x-zip-compressed';
    $dataArray = array(
    array('unitTest', 'my project description with thumbnail of type JPG in images folder.', $testFile, $fileName, $fileChecksum, $fileSize, $fileType)
    );
    return $dataArray;
  }

  public function correctPostDataThumbailInRootFolderPNG() {
    $fileName = 'test_thumbnail_png.zip';
    $testFile = dirname(__FILE__).'/testdata/'.$fileName;
    $fileChecksum = md5_file($testFile);
    $fileSize = filesize($testFile);
    $fileType = 'application/x-zip-compressed';
    $dataArray = array(
    array('unitTest', 'my project description with thumbnail of type PNG in root folder.', $testFile, $fileName, $fileChecksum, $fileSize, $fileType)
    // array('unitTest', 'my project description with thumbnail in images folder.', $testFile, $fileName, $fileChecksum, $fileSize, $fileType),
    );
    return $dataArray;
  }

  public function correctPostDataThumbailInImagesFolderPNG() {
    $fileName = 'test_thumbnail_png_in_images.zip';
    $testFile = dirname(__FILE__).'/testdata/'.$fileName;
    $fileChecksum = md5_file($testFile);
    $fileSize = filesize($testFile);
    $fileType = 'application/x-zip-compressed';
    $dataArray = array(
    array('unitTest', 'my project description with thumbnail of type PNG in images folder.', $testFile, $fileName, $fileChecksum, $fileSize, $fileType)
    );
    return $dataArray;
  }

  public function incorrectPostDataWithThumbnail() {
    $fileName = 'test_missing_thumbnail.zip';
    $testFile = dirname(__FILE__).'/testdata/'.$fileName;
    $fileChecksum = md5_file($testFile);
    $fileSize = filesize($testFile);
    $fileType = 'application/x-zip-compressed';
    $dataArray = array(
    array('unitTest', 'my project description without thumbnail of type JPG.', $testFile, $fileName, $fileChecksum, $fileSize, $fileType)
    );
    return $dataArray;
  }

}
?>
