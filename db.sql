create database BootstraPHPed_Blog;

use BootstraPHPed_Blog;


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
	UNIQUE (super,name),
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

CREATE TABLE IF NOT EXISTS posts (
	id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	title VARCHAR(128) NOT NULL,
	content VARCHAR(512) NOT NULL,
	author INT UNSIGNED NOT NULL,
	published_on DATETIME NOT NULL,
	
	FOREIGN KEY (author) REFERENCES users(id)
);




-- Constraints: 
-- 1. roles have to be sorted by privileges
-- 2. id > 1
-- Constraint #1 lets you easily select "all the users with privileges greater than level X"
-- Eg. $role >= 2 identifies all the registered users
INSERT INTO roles (id, name) VALUES
	( 1, 'guest' ),
	( 2, 'user' ),
	( 3, 'supervisor' ),
	( 4, 'admin' )
;

-- define (super)sections
INSERT INTO sections (id, name, title, min_role, sort) VALUES
	( 1, 'blog', 'Blogging', 2, 1 ),
	( 2, 'stats', 'Stats (requires privileges)', 3, 2 ),
	( 3, 'admin', '::ADMIN::', 4, 3 ),
	( 4, 'faq', '<i class="icon-question-sign icon-white"></i> FAQ', 1, 4 )
;
-- define subsections
INSERT INTO sections (super, name, title, min_role, sort) VALUES
	-- section 'blog'
	( 1, 'my', 'My Blog', NULL, 1 ),
	( 1, 'team', 'My Team\'s blog', NULL, 2 ),
	( 1, 'stats', 'Stats (privileged)', 3, 3 ),
	-- section 'admin'
	( 3, 'user', 'Admin users', NULL, 1 ),
	( 3, 'section', 'Admin sections', NULL, 2 )
;


INSERT INTO users (id, email,nickname,password,role,team) VALUES
	( 1, 'u1t1@localhost.localdomain', 'U1T1', MD5('pass'), 4, 'team1' ),
	( 2, 'u2t1@localhost.localdomain', 'U2T1', MD5('pass'), 2, 'team1' ),
	( 3, 'u1t2@localhost.localdomain', 'U1T2', MD5('pass'), 2, 'team2' ),
	( 4, 'u2t2@localhost.localdomain', 'U2T2', MD5('pass'), 3, 'team2' )
;

INSERT INTO posts (author,title,content,published_on) VALUES
	(1, 'titolo first post', 'lorem ipsus loquer', NOW() ),
	(2, 'titolo first post 2', 'mare magnum', NOW() ),
	(2, 'asfa awef', 'asjofawph wuah ahuaèwq gw9egfvw euhrhgf eqgtefg', NOW() ),
	(4, 'titolo', 'marsdfve magnum', NOW() ),
	(3, 'titolo', 'mare magasgvae aer gaegeth num', NOW() )
;
