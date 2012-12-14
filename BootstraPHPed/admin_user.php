<?php

require_once("session.php");

?>

				<h1>Admin users</h1>
				<h2>Crea nuova utenza locale</h2>

				<div id="form_adduser-results"></div>

				<form id="form_adduser" method="post" class="form-horizontal">
					<div class="control-group">
						<label class="control-label" for="email">Email:</label>
						<div class="controls">
							<input placeholder="Email address" rel="tooltip" data-placement="right" type="text" name="email" id="email" title="Deve corrispondere all'email di un utente su RT (no utenze di dominio!)" />
						</div>
					</div>
					<div class="control-group">
						<div class="controls">
							<button type="submit" class="btn">Crea utente</button>
						</div>
					</div>
				</form>
				
				
				

				<h2>Modifica utente</h2>

				<div id="form_edituser-results"></div>

				<form id="form_edituser" method="post">
					<label class="control-label" for="ambiente">Email:</label>
					<input name="email" id="email" placeholder="Email address" type="text" data-provide="typeahead">
					<button type="submit" class="btn">Modifica</button>
				</form>
				
				<input id="email-typeahead" type="text" class="span3" style="margin: 0 auto;" data-provide="typeahead">
			
<!--
data-source="[&quot;Alabama&quot;,&quot;Alaska&quot;,&quot;Arizona&quot;,&quot;Arkansas&quot;,&quot;California&quot;,&quot;Colorado&quot;,&quot;Connecticut&quot;,&quot;Delaware&quot;,&quot;Florida&quot;,&quot;Georgia&quot;,&quot;Hawaii&quot;,&quot;Idaho&quot;,&quot;Illinois&quot;,&quot;Indiana&quot;,&quot;Iowa&quot;,&quot;Kansas&quot;,&quot;Kentucky&quot;,&quot;Louisiana&quot;,&quot;Maine&quot;,&quot;Maryland&quot;,&quot;Massachusetts&quot;,&quot;Michigan&quot;,&quot;Minnesota&quot;,&quot;Mississippi&quot;,&quot;Missouri&quot;,&quot;Montana&quot;,&quot;Nebraska&quot;,&quot;Nevada&quot;,&quot;New Hampshire&quot;,&quot;New Jersey&quot;,&quot;New Mexico&quot;,&quot;New York&quot;,&quot;North Dakota&quot;,&quot;North Carolina&quot;,&quot;Ohio&quot;,&quot;Oklahoma&quot;,&quot;Oregon&quot;,&quot;Pennsylvania&quot;,&quot;Rhode Island&quot;,&quot;South Carolina&quot;,&quot;South Dakota&quot;,&quot;Tennessee&quot;,&quot;Texas&quot;,&quot;Utah&quot;,&quot;Vermont&quot;,&quot;Virginia&quot;,&quot;Washington&quot;,&quot;West Virginia&quot;,&quot;Wisconsin&quot;,&quot;Wyoming&quot;]"
-->
				
	<script>
		$(document).ready(function() {
		
		
			$("#email").tooltip();			
			$("#form_adduser").submit(function() {
				$("#form_adduser-results").removeClass().html('<img src="img/ui-anim_basic_16x16.gif">');
				$("#form_adduser-results").load('./models/newUser.php', $("#form_adduser").serializeArray(), function(response) {
					regex = /^OK/;
					if(regex.test(response)) { // la creazione dell'utente e' riuscita
						$("#form_adduser-results").removeClass().addClass("alert alert-success");
						$("#form_adduser-results").html(response);
					}
					else {
						//stampa errori
						$("#form_adduser-results").removeClass().addClass('alert alert-error');
						$("#form_adduser-results").html('<i class="icon-warning-sign"></i> <strong>Errori nella compilazione del form</strong>:' + response);
					}
				});
				return false; // non inviare il form: ci pensa AJAX!
			});
			
			
			
			
		
		
			var options = { minLength: 2 };
			//$("#email-typeahead").typeahead(options);
			
			$('.typeahead').typeahead({
				source: function (typeahead, query) {
					return $.get('/typeahead', { query: query }, function (data) {
						return typeahead.process(data);
					});
				}
			});
			
			$("#form_edituser").submit(function() {
				$("#form_edituser-results").removeClass().html('<img src="img/ui-anim_basic_16x16.gif">');
				$("#form_edituser-results").load('./models/newUser.php', $("#form_edituser").serializeArray(), function(response) {
					regex = /^OK/;
					if(regex.test(response)) { // la creazione dell'utente e' riuscita
						$("#form_edituser-results").removeClass().addClass("alert alert-success");
						$("#form_edituser-results").html(response);
					}
					else {
						//stampa errori
						$("#form_edituser-results").removeClass().addClass('alert alert-error');
						$("#form_edituser-results").html('<i class="icon-warning-sign"></i> <strong>Errori nella compilazione del form</strong>:' + response);
					}
				});
				return false; // non inviare il form: ci pensa AJAX!
			});
		});
	</script>