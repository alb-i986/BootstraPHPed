<?php
/**
 *
 * @author Alberto 'alb-i986' Scotto
 */

?>
				<h1>Il mio blog</h1>
				
				<div id="form-results"></div>

				<div class="well">
					Legenda:
					<ul>
						<li>Riga con sfondo rosso: ambiente schedulato per la cancellazione</li>
						<li>Data di scadenza in rosso: ambiente scaduto</li>
					</ul>
				</div>
			
					<table class="table table-striped table-hover table-condensed">
						<thead>
							<tr>
								<th>Title</th>
								<th>Content</th>
								<th>Published On</th>
								<th>Edit</th>
								<th>Delete</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
						</tfoot>
						<tbody>
						
<?php
	$user_props = sess_getUser()->get(array('id'));
	$posts = $dao->getPostsByAuthor( $user_props['id'] );

	foreach($posts as $post) {
/*
		if( $dao->isPostDeletable($post['id']) )
			echo '<tr class="error">' ."\n";
		else
*/			echo '<tr>' ."\n";

		if( true ) {
			echo '<td class="text-error">';
		} else {
			echo '<td>';
		}
		echo '<a href="https://example.com/'. $post['title'] .'/" target="_new">' . $post['title'] . '</a>';
		echo '</td>';

		echo '<td>';
		echo $post['content'];
		echo '</td>';

		echo '<td>';
		$published_on = new DateTime($post['published_on']);
		echo $published_on->format("d/m/Y h.m");
		echo '</td>';

		echo '<td>';
?>
								<form class="form_editpost" id="form_editpost_<?php echo $post['id']?>" method="post">
			                        <input type="hidden" name="post_id" value="<?php echo $post['id']?>">
									<button type="submit" class="btn btn-small btn-flag-deletable" rel="tooltip"><i class="icon-edit"></i></button>
								</form>
<?php
		echo '</td>';

		echo '<td>';
?>
								<form class="form_cancella" id="form_cancella_<?php echo $post['id']?>" method="post">
			                        <input type="hidden" name="post_id" value="<?php echo $post['id']?>">
									<button type="submit" class="btn btn-small btn-flag-deletable" rel="tooltip"><i class="icon-trash"></i></button>
								</form>
<?php
		echo '</td>';

		echo "</tr>\n";
	}
?>
						</tbody>
					</table>

<script>
	$(document).ready(function() {

		$("button.btn-flag-deletable").tooltip({title: 'Non lo cancella subito ma il prossimo weekend'});

		$("form.form_proroga").each(function(){
			var form_id = $(this).attr("id");
			var form_selector = "#" + form_id;

			$( form_selector ).submit(function() {
				$("#form-results").load('<?php echo FORM_HANDLERS_RELPATH ?>proroga_amb.php', $(form_selector).serializeArray(), function(response) {
					var regex_formerrors = /^<ul>/;
					if(response == "true") {
						$("#form-results").removeClass().addClass("alert alert-success");
						$("#form-results").html("Ambiente prorogato");
					} else if(regex_formerrors.test(response)) { // errori di validazione dell'input
						$("#form-results").removeClass().addClass('alert alert-warning');
						$("#form-results").html('<i class="icon-exclamation-sign"></i> <strong>Errori di validazione dell\'input</strong>: '+ response);
					} else {
	                    $("#form-results").removeClass().addClass('alert alert-error');
	                    $("#form-results").html('<i class="icon-warning-sign"></i> '+ response);
					}
				});
				return false; // non inviare il form: ci pensa AJAX!
			});
		});


		$("form.form_cancella").each(function(){
			var form_id = $(this).attr("id");
			var form_selector = "#" + form_id;

			$( form_selector ).submit(function() {
				$("#form-results").load('<?php echo FORM_HANDLERS_RELPATH ?>flag_amb.php', $(form_selector).serializeArray(), function(response) {
					var regex_formerrors = /^<ul>/;
					if(response == "true") {
						$("#form-results").removeClass().addClass("alert alert-success");
						$("#form-results").html("La rimozione dell'ambiente e' stata schedulata per il prossimo weekend.");
					} else if(response == "none") {
						$("#form-results").html("");
						// l'utente ha cliccato il bottone 'convalida' senza aver checkato nessun ambiente => OK
					}
					else if(regex_formerrors.test(response)) { // Errori di validazione dell'input
						$("#form-results").removeClass().addClass('alert alert-warning');
						$("#form-results").html('<i class="icon-exclamation-sign"></i> <strong>Errori di validazione dell\'input</strong>: '+ response);
					} else {
	                    $("#form-results").removeClass().addClass('alert alert-error');
	                    $("#form-results").html('<i class="icon-warning-sign"></i> '+ response);
					}
				});
				return false; // non inviare il form: ci pensa AJAX!
			});
		});
	});

</script>
