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
<body>
  <script type="text/javascript">
  function removeipform(id, name) {
    if (confirm("Remove blocking of IP-Address '"+name+"'?"))
      document.getElementById(id).submit();
  }
  </script>
  <h2>Administration Tools - List of blocked IP-Addresses</h2>
  <a id="aAdminToolsBackToCatroidweb" href="<?php echo BASE_PATH;?>admin/tools">&lt;- back</a><br /><br />
  <?php if($this->answer) {
    echo 'Answer:<br/>'.$this->answer.'<br /><br/>';
  }?>
  <div class="projectList">

			  Add new IP-Address to block: 
			  <form id="newblockipform<?php echo $ipN ?>" class="admin" action="addBlockedIp" method="POST">
          <input type="text" name="blockip" value=""/>
          <input type="button" value="add IP-Address" name="addButton" id="add<?php $ipN ?>" onclick="javascript:submitRemoveIpForm('removeipform<?php echo $ipN ?>', '<?php echo $ip ?>');" /> <!-- chg -->
        </form>

			<br/>
			<br/>

      <table class="projectTable">
        <tr>
          <th>IP-Address</th>
          <th>Remove IP-blocking</th>
        </tr>
      <?php
        if($this->blockedips) {
        foreach($this->blockedips as $blockedip) {
          $ip = $blockedip['ip_address'];
          $ipN = preg_replace("/\./", "_", $ip);
          ?>
        <tr>
          <td><?php echo $ip ?></td>
          <td>
            <form id="removeipform<?php echo $ipN ?>" class="admin" action="removeBlockedIp" method="POST">
              <input type="hidden" name="blockedIp" value="<?php echo $ip ?>"/>
              <input type="hidden" name="remove" value="remove"/>
              <input type="button" value="remove" name="removeButton" id="removeip<?php $ipN ?>" onclick="javascript:submitRemoveIpForm('removeip<?php echo $ipN ?>', '<?php echo $ip ?>');" /> <!-- chg -->
            </form>
          </td>
        </tr>
      <?php }}?>
      </table>
  </div>
</body>