<?php
/**
 *
 * @author Alberto 'alb-i986' Scotto
 */

//namespace BootstraPHPed\classes\Persistence;


class DAO extends DAO_Core {


	public function getPost($post) {
		if( empty($post) )
			throw new InvalidArgumentException('Mandatory argument is empty: post');

		$sql = "SELECT * FROM posts WHERE id=$post";
		$row = $this->db->GetRow($sql);
		if($row === FALSE) {
			$this->logger->error( __CLASS__ . '' . __METHOD__ . ':'. __LINE__ .' ADOdb GetRow failed. SQL: '.$sql );
			throw new ADOdbException('GetRow failed');
		}
		return $row;
	}

	public function insertPost($title, $content, $author) {
		if( empty($title) || empty($content) || empty($author) )
			throw new InvalidArgumentException('Mandatory argument is empty: title | content | author');

		$sql = "INSERT INTO posts (title, content, author) VALUES ( '$title', '$content', '$author' )";
		$results = $this->db->Execute($sql);
		if ( !$results || $this->db->Affected_Rows()!= 1 ) {
			$this->logger->error( __CLASS__ . '' . __METHOD__ . ':'. __LINE__ .' ADOdb INSERT failed. SQL: '.$sql.' Err: '.$this->db->ErrorMsg() );
			throw new ADOdbException('INSERT failed');
		}
		return true;
	}


	public function getPostsByAuthor($author) {
		if( empty($author) )
			throw new InvalidArgumentException('Mandatory argument is empty: author');
		$author_row = $this->getAuthor( $author );
		if( empty($author_row) )
			throw new InvalidArgumentException('Invalid argument: specificed author does not exist in the DB');
			
		$sql = "SELECT * FROM posts WHERE author=". $author_row['id'];
		return $this->db->GetAll($sql);
	}

	public function getPostsByTeam( $team ) {
		if( empty($team) )
			throw new InvalidArgumentException('Mandatory argument is empty: team');
			
		$sql = "SELECT * FROM posts p JOIN users u ON p.author=u.id WHERE u.team='$team'";
		return $this->db->GetAll($sql);
	}


	public function getAuthors() {
		$sql = "SELECT * FROM users";
		return $this->db->GetAll($sql);
	}

	public function getAuthor($author) {
		return $this->getUser($author);
	}


	public function setPost($post, $fields) {
		if( empty($post) || empty($fields) )
			throw new InvalidArgumentException('Mandatory argument is empty: post | fields');
		$post_row = $this->getPost($post);
		if( empty($post_row) )
			throw new InvalidArgumentException('Invalid argument: specificed post does not exist in the DB');

		$sql = "UPDATE posts SET deletable=1 WHERE deletable=0 AND requestor='$requestor_id' ";
		$res = $this->db->Execute($sql);
		if ( count($ambienti) == $this->db->Affected_Rows() )
			return true;
		else
			return false;
	}

	public function delPost($post) {
		if( empty($post) )
			throw new InvalidArgumentException('Mandatory argument is empty: post');
		$post_row = $this->getPost($post);
		if( empty($post_row) )
			throw new InvalidArgumentException('Invalid argument: specificed post does not exist in the DB');

		$sql = "DELETE FROM posts WHERE id=?";
		$stmt = $this->db->Prepare($sql);
		
		$res = $this->db->Execute($stmt, array($id));
		if( !$res ) {
			$this->logger->error( __CLASS__ . '' . __METHOD__ . ':'. __LINE__ .' ADOdb DELETE post failed. SQL: '.$sql );
			return false;
		} else {
			return true;
		}
	}



}