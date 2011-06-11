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

var Registration = Class.$extend( {
  __include__ : [__baseClassVars],
  __init__ : function() {
    $("#registrationFormDialog").toggle(true);
    $("#registrationFormAnswer").toggle(false);
    $("#registrationSubmit").click(
      $.proxy(this.registrationSubmit, this));
    $("#registrationUsername").keypress(
      $.proxy(this.registrationCatchKeypress, this));
    $("#registrationPassword").keypress(
      $.proxy(this.registrationCatchKeypress, this));
    $("#registrationEmail").keypress(
      $.proxy(this.registrationCatchKeypress, this));
    $("#registrationCountry").keypress(
      $.proxy(this.registrationCatchKeypress, this));
    $("#registrationCity").keypress(
      $.proxy(this.registrationCatchKeypress, this));
    $("#registrationMonth").keypress(
      $.proxy(this.registrationCatchKeypress, this));
    $("#registrationYear").keypress(
        $.proxy(this.registrationCatchKeypress, this));
    $("#registrationGender").keypress(
        $.proxy(this.registrationCatchKeypress, this));    
  },
  
  registrationSubmit : function() {
    $("#registrationInfoText").toggle(false);
    this.disableForm();
    var url = this.basePath + 'catroid/registration/registrationRequest.json';
    $.ajax({
      type : "POST",
      url : url,
      data : ({
        registrationUsername : $("#registrationUsername").val(),
        registrationPassword : $("#registrationPassword").val(),
        registrationEmail : $("#registrationEmail").val(),
        registrationCountry : $("#registrationCountry").val(),
        registrationCity : $("#registrationCity").val(),
        registrationMonth : $("#registrationMonth").val(),
        registrationYear : $("#registrationYear").val(),
        registrationGender : $("#registrationGender").val()
      }),
      timeout : (5000),
      success : jQuery.proxy(this.registrationSuccess, this),
      error : jQuery.proxy(this.registrationError, this)
    });
  },

  registrationSuccess : function(response) {
    if(response.statusCode == 200) {
      location.href = this.basePath+'catroid/profile';
    } else {
      $("#registrationInfoText").toggle(true);
      $("#registrationErrorMsg").html(response.answer);
      this.enableForm();
    }
  },
  
  registrationError : function(response, errCode) {
    alert("registrationError");
    this.enableForm();
  },
  
  registrationCatchKeypress : function(event) {
    if(event.which == '13') {
      event.preventDefault();
      this.registrationSubmit();
    }
  },
  
  disableForm : function() {
    $("#registrationUsername").attr("disabled", "disabled");
    $("#registrationPassword").attr("disabled", "disabled");
    $("#registrationEmail").attr("disabled", "disabled");
    $("#registrationCountry").attr("disabled", "disabled");
    $("#registrationCity").attr("disabled", "disabled");
    $("#registrationMonth").attr("disabled", "disabled");
    $("#registrationYear").attr("disabled", "disabled");
    $("#registrationGender").attr("disabled", "disabled");
    $("#registrationSubmit").attr("disabled", "disabled");
  },
  
  enableForm : function() {
    $("#registrationUsername").removeAttr("disabled");
    $("#registrationPassword").removeAttr("disabled");
    $("#registrationEmail").removeAttr("disabled");
    $("#registrationCountry").removeAttr("disabled");
    $("#registrationCity").removeAttr("disabled");
    $("#registrationMonth").removeAttr("disabled");
    $("#registrationYear").removeAttr("disabled");
    $("#registrationGender").removeAttr("disabled");
    $("#registrationSubmit").removeAttr("disabled");
  }

});
