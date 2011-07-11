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
?>
  <script type="text/javascript">
  	$(document).ready(function() {
  		new Profile();
  	});
  </script>
  
  	<div class="webMainMiddle">
  		<div class="blueBoxMain">
  		   	<div class="webMainContent">
              <div class="webMainContentTitle">
                <?php echo $this->languageHandler->getString('title', $this->requestedUser)?>
              </div> <!-- webMainContentTitle --> 
						  <div class="profileMain">            	
            	  <div class ="whiteBoxMain">
            	    <div class="profileText">
            	    <div class="profileFormContainer">
            	    	<div class="profileFormAnswer" id="profileFormAnswer">
            	    		<div class="errorMsg" id="errorMsg">
            	    		<!-- error messages here -->
              		   	</div>
            	    		<div class="okMsg" id="okMsg">
            	    		<!-- ok messages here -->
              		   	</div>
            		   	</div>
            		   	<br>
            		   	<?php 
                      if($this->ownProfile) {            		   	
            		   	?>
                  	<form method="POST" name="profileFormDialog" id="profileFormDialog" action="">
                      <input type="hidden" id="profileUser" name="profileUser" value="<?php echo $this->requestedUser; ?>">
          		   			<div id="profilePasswordDiv">
                        <a href="javascript:;" class="profileText" id="profileChangePasswordOpen"><?php echo $this->languageHandler->getString('password')?></a><br>
          		   			</div>  
                      <div id="profilePasswordDivOpened">
          		   			  <a href="javascript:;" class="profileText" id="profileChangePasswordClose"><?php echo $this->languageHandler->getString('password')?></a><br>
                        <input type="text" id="profileOldPassword" name="profileOldPassword" value="<?php echo htmlspecialchars($this->postData['profileOldPassword']); ?>" required="required" placeholder="<?php echo $this->languageHandler->getString('old_password')?>" ><br>
          		   			  <input type="text" id="profileNewPassword" name="profileNewPassword" value="<?php echo htmlspecialchars($this->postData['profileNewPassword']); ?>" required="required" placeholder="<?php echo $this->languageHandler->getString('new_password')?>" ><br>
          		   			  <input type="button" name="profilePasswordSubmit" id="profilePasswordSubmit" value="<?php echo $this->languageHandler->getString('send')?>" class="button orange compact profileSubmitButton">
          		   			</div>
          		   			<br>
          		   			<div id="profileEmailTextDiv">
                        <?php 
                          $x = 0;
                          $emails_count = $this->userEmailsCount;
                          for($x; $x < $emails_count; $x++) {
                            if($x < $emails_count-1) { ?>
            		   			      <a href="javascript:;" class="profileText" id="<?php echo $x ?>"><?php echo $x.$this->userEmail ?></a> <br>
                        <?php }
                            else { ?>
                              <a href="javascript:;" class="profileText" id="<?php echo $x ?>"><?php echo $x.$this->userEmail ?></a> <input type="button" name="profileAddNewEmailField" id="profileAddNewEmailField" value=" + " class="button orange compact profileSubmitButton"> <input type="button" name="profileRemoveNewEmailField" id="profileRemoveNewEmailField" value=" _ " class="button orange compact profileSubmitButton"><br>
                        <?php }
                          }
                        ?>
          		   			</div>
                      <div id="profileEmailChangeDiv">
                        <a href="javascript:;" class="profileText" id="profileChangeEmailClose"><?php echo $this->languageHandler->getString('email')?></a><br>
                        <input type="email" id="profileEmail" name="profileEmail" value="" required="required" placeholder="<?php //echo $this->languageHandler->getString('new_email')?>" ><br>
                        <input type="button" name="profileEmailSubmit" id="profileEmailSubmit" value="<?php echo $this->languageHandler->getString('send')?>" class="button orange compact profileSubmitButton">
                      </div>
                      <div id="profileAdditionalEmailInputFields">
                        <div id="emailTextFields">
                        </div>
                        <div id="emailAddButton">
                          <input type="button" name="profileAddEmailButton" id="profileAddEmailButton" value="<?php echo $this->languageHandler->getString('add')?>" class="button orange compact profileSubmitButton">
                        </div>
                      </div>
          		   			<br>
          		   			<div id="profileCountryDiv">
          		   			  <a href="javascript:;" class="profileText" id="profileChangeCountryClose"><?php echo $this->languageHandler->getString('country')?></a><br>
          		   			  <select id="profileCountry" name="profileCountry" class="profile" required="required" >
              		   		<?php // country 
              		   			$x = 0;
              		   			$sumCount = count($this->countryCodeList);
              		   			while($x < $sumCount+1) {
              		   			  if($x == 0) {
                              echo '<option value="0" >select your country</option>\r';
                            }
              		   			  else if(strcmp($this->countryCodeList[$x], $this->userCountryCode) == 0) {
                              echo "<option value=\"" . $this->countryCodeList[$x] . "\" selected >" . $this->countryNameList[$x] . "</option>\r";
                            }
                            else {
                              echo "<option value=\"" . $this->countryCodeList[$x] . "\" >" . $this->countryNameList[$x] . "</option>\r";
                            }
                            $x++;           
              		   			}
              		   		?>
          		   			</select><br>
          		   			<input type="button" name="profileCountrySubmit" id="profileCountrySubmit" value="<?php echo $this->languageHandler->getString('send')?>" class="button orange compact profileSubmitButton">
          		   			</div>
											<div id="profileCountryTextDiv">
              		   		<?php 
              		   			$x = 0;
              		   			$sumCount = count($this->countryCodeList);
              		   			while($x < $sumCount+1) {
              		   			  if(strcmp($this->countryCodeList[$x], $this->userCountryCode) == 0) {
                              echo 'from <a href="javascript:;" class="profileText" id="profileChangeCountryOpen">'.$this->countryNameList[$x].'</a>';
                              break;
                            }
                            $x++;           
              		   			}
              		   		?>
              		   	  <br>
											</div>
                      <br>
            		   	</form>
            		   	<?php 
                      }
                      else {
                    ?>
                    <div id="profileCountryTextDiv">
          		   		<?php 
          		   			$x = 0;
          		   			$sumCount = count($this->countryCodeList);
          		   			while($x < $sumCount+1) {
          		   			  if(strcmp($this->countryCodeList[$x], $this->userCountryCode) == 0) {
                          echo 'from '.$this->countryNameList[$x];
                          break;
                        }
                        $x++;           
          		   			}
          		   	  ?>
          		   		</div>
                    <?php
                      }
          		   		?>
	          		   		<br>
          		   			<br>
          		   			<br>
          		   			<br>
										
                  </div> <!-- profileFormContainer -->
								</div> <!-- profile Text -->
              </div> <!--  White Box -->            	
           </div> <!--  license Main -->  		   		
  		  </div> <!-- mainContent close //-->
  		</div> <!-- blueBoxMain close //-->
  	</div>

