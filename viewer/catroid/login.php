<?php
/*
 * Catroid: An on-device visual programming system for Android devices
 * Copyright (C) 2010-2013 The Catrobat Team
 * (<http://developer.catrobat.org/credits>)
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * 
 * An additional term exception under section 7 of the GNU Affero
 * General Public License, version 3, is available at
 * http://developer.catrobat.org/license_additional_term
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

?>
  <script type="text/javascript">
  	$(document).ready(function() {
      var languageStringsObject = { 
          "username_missing" : "<?php echo $this->module->errorHandler->getError('userFunctions', 'username_missing'); ?>",
          "password_missing" : "<?php echo $this->module->errorHandler->getError('userFunctions', 'password_missing'); ?>"
          };
      new Login(languageStringsObject);
  	});
  </script>
  <article>
    <div class="webMainContentTitle"><?php echo $this->languageHandler->getString('title'); ?></div>
      <div class="loginMain">            	
  	    <div class="loginText">
          <div class="loginHeader"><?php echo $this->languageHandler->getString('please_enter_nick_and_password'); ?></div>
          <?php echo $this->languageHandler->getString('nickname'); ?><br />
          <input id="loginUsername" type="text" class="catroid webHeadLoginBox" placeholder="<?php echo $this->languageHandler->getString('enter_nick'); ?>" required="required" /><br />
          <?php echo $this->languageHandler->getString('password'); ?><br />
          <input id="loginPassword" type="password" class="catroid webHeadLoginBox" placeholder="<?php echo $this->languageHandler->getString('enter_password'); ?>" required="required" /><br />
          <input id="loginSubmitButton" type="button" class="catroidSubmit button orange loginSubmitButton" value="<?php echo $this->languageHandler->getString('login'); ?>" /><br />
          
          <div class="otherOptions"><?php echo $this->languageHandler->getString('additional_options'); ?></div>
          <ul class="loginOptions">
            <li><a id="forgotPassword" href="<?php echo BASE_PATH?>catroid/passwordrecovery"><?php echo $this->languageHandler->getString('password_link')?></a></li>
            <li><a id="signUp" href="<?php echo BASE_PATH?>catroid/registration"><?php echo $this->languageHandler->getString('account_link')?></a></li>
          </ul>
      </div> <!-- login Text -->         	
 </div> <!--  login Main -->  		   		
  	</div>
  </article>