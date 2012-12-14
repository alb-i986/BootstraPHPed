create database bootstraphped;

use bootstraphped;


CREATE TABLE IF NOT EXISTS roles (
	id INT UNSIGNED PRIMARY KEY,
	name VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS sections (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(255) NOT NULL,
	title VARCHAR(255) NOT NULL,
	super INT UNSIGNED DEFAULT NULL,
	min_role INT UNSIGNED DEFAULT 1,
	sort INT UNSIGNED NOT NULL DEFAULT 1,
	
	FOREIGN KEY (min_role) REFERENCES roles(id),
	FOREIGN KEY (super) REFERENCES sections(id),
	UNIQUE (super,name)
	UNIQUE (super,sort) -- restricts sorting of (super|sub)sections
);

CREATE TABLE IF NOT EXISTS users (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	email VARCHAR(64) NOT NULL UNIQUE,
	nickname VARCHAR(32) NOT NULL UNIQUE,
	password VARCHAR(255) NOT NULL,
	role INT UNSIGNED NOT NULL DEFAULT 1,
	team VARCHAR(50) NOT NULL,
	
	FOREIGN KEY (role) REFERENCES roles(id)
);




-- Roles have to be sorted by levels of powers
-- In this way, you are able to easily select "all the users with a level of privileges greater than level X"
-- Eg. $role >= 2 identifies all the registered users
INSERT INTO roles (id, name) VALUES
	( 1, 'guest' ),
	( 2, 'user' ),
	( 4, 'supervisor' ),
	( 4, 'admin' )
;

-- define (super)sections
INSERT INTO sections (id, name, title, min_role, sort) VALUES
	( 1, 'section1', 'Section1 (requires login)', 2, 1 ),
	( 2, 'section2', 'Section2 (requires privileges)', 3, 2 ),
	( 3, 'admin', '::ADMIN::', 4, 3 ),
	( 4, 'faq', '<i class="icon-question-sign icon-white"></i> FAQ <i class="icon-question-sign icon-white"></i>', 1, 4 )
;
-- define subsections
INSERT INTO sections (super, name, title, min_role, sort) VALUES
	-- section 'section1'
	( 1, 'sub11', 'Subsection 1.1', NULL, 1 ),
	( 1, 'sub12', 'Subsection 1.2', NULL, 2 ),
	( 1, 'sub13', 'Subsection 1.3 (privileged)', 3, 3 ),
	-- section 'admin'
	( 3, 'user', 'Admin users', NULL, 1 ),
	( 3, 'section', 'Admin sections', NULL, 2 )
;


INSERT INTO users (email,nickname,password,role,team) VALUES
	( 'u1t1@localhost', 'U1T1', MD5('pass'), 4, 'team1' ),
	( 'u2t1@localhost', 'U2T1', MD5('pass'), 2, 'team1' ),
	( 'u1t2@localhost', 'U1T2', MD5('pass'), 2, 'team2' ),
	( 'u2t2@localhost', 'U2T2', MD5('pass'), 3, 'team2' )
;