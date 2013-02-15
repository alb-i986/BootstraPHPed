<?php
/**
 *
 * @author Alberto 'alb-i986' Scotto
 */

?>

				<h1>I post del mio team</h1>
				

				<div class="well">
					Legenda:
					<ul>
						<li>Riga con sfondo rosso: ambiente schedulato per la cancellazione</li>
						<li>Data di scadenza in rosso: ambiente scaduto</li>
					</ul>
				</div>
				
				<table class="table table-striped table-hover">
					<thead>
							<tr>
								<th>Author</th>
								<th>Title</th>
								<th>Content</th>
								<th>Published On</th>
								<th>Edit</th>
								<th>Delete</th>
							</tr>
					</thead>
						<tbody>
						
<?php
	$posts = $dao->getPostsByTeam( sess_getUser()->getTeam() );

	foreach($posts as $post) {
/*
		if( $dao->isPostDeletable($post['id']) )
			echo '<tr class="error">' ."\n";
		else
*/			echo '<tr>';

		echo '<td>';
		$user_row = $dao->getUser( $post['author'] );
		echo $user_row['nickname'];
		echo '</td>';


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
		$published_on = new DateTime( $post['published_on'] );
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

		echo '</tr>';
	}
?>
						</tbody>
				</table>
