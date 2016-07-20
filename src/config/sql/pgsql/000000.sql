-- ---------------------------------
-- Install SQL
-- 
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------


-- ----------------------------
--  institution
-- ----------------------------
CREATE TABLE IF NOT EXISTS institution (
  id SERIAL PRIMARY KEY,
  name VARCHAR(255),
  email VARCHAR(255),
  description TEXT,
  logo VARCHAR(255),
-- TODO: location information?
-- TODO: Contact information?
  active NUMERIC(1) NOT NULL DEFAULT 1,
  hash VARCHAR(255),
  del NUMERIC(1) NOT NULL DEFAULT 0,
  modified TIMESTAMP DEFAULT NOW(),
  created TIMESTAMP DEFAULT NOW()
);

-- ----------------------------
--  user
-- ----------------------------
CREATE TABLE IF NOT EXISTS "user" (
  id SERIAL PRIMARY KEY,
  institution_id INTEGER NULL DEFAULT NULL,    -- 0 = site user not belonging to any institution....
  uid VARCHAR(64),
  username VARCHAR(64),
  password VARCHAR(64),
  -- ROLES: 'admin', 'client', 'staff', 'student
  role VARCHAR(64),
  name VARCHAR(255),
  email VARCHAR(255),
  active NUMERIC(1) NOT NULL DEFAULT 1,
  hash VARCHAR(255),
  notes TEXT,
  last_login TIMESTAMP DEFAULT NULL,
  del NUMERIC(1) NOT NULL DEFAULT 0,
  modified TIMESTAMP DEFAULT NOW(),
  created TIMESTAMP DEFAULT NOW(),
  -- NOTE Cannot have a foreign key as it does not allow for a 0|null value which is required for local site users....
	-- FOREIGN KEY (institution_id) REFERENCES institution(id) ON DELETE CASCADE,
  CONSTRAINT uid UNIQUE (institution_id, uid),
  CONSTRAINT username UNIQUE (institution_id, username),
  CONSTRAINT email UNIQUE (institution_id, email),
  CONSTRAINT hash UNIQUE (institution_id, hash)
);

-- ----------------------------
--  user_owns_institution
-- ----------------------------
CREATE TABLE IF NOT EXISTS user_owns_institution (
	user_id INTEGER NOT NULL,
	institution_id INTEGER NOT NULL,
	FOREIGN KEY (user_id) REFERENCES "user"(id) ON DELETE CASCADE,
	FOREIGN KEY (institution_id) REFERENCES institution(id)  ON DELETE CASCADE
);

-- ----------------------------
--  Course Data Tables
-- ----------------------------
CREATE TABLE IF NOT EXISTS course (
  id SERIAL PRIMARY KEY,
  institution_id INTEGER NOT NULL DEFAULT 0,
  name VARCHAR(255),
  code VARCHAR(64),
  email VARCHAR(255),
  description TEXT,
  start TIMESTAMP DEFAULT NOW(),
  finish TIMESTAMP DEFAULT NOW(),
  active NUMERIC(1) NOT NULL DEFAULT 1,
  del NUMERIC(1) NOT NULL DEFAULT 0,
  modified TIMESTAMP DEFAULT NOW(),
  created TIMESTAMP DEFAULT NOW(),
  CONSTRAINT code_institution UNIQUE (code, institution_id)
);

-- ----------------------------
-- For now we will assume that one user has one role in a course, ie: coordinator, lecturer, student
-- ----------------------------
CREATE TABLE IF NOT EXISTS user_course (
  user_id INTEGER NOT NULL,
  course_id INTEGER NOT NULL,
  CONSTRAINT user_course_key UNIQUE (user_id, course_id),
  FOREIGN KEY (user_id) REFERENCES "user"(id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES course(id) ON DELETE CASCADE
);


-- ----------------------------
--  TEST DATA
-- ----------------------------

INSERT INTO institution (name, email, description, logo, active, hash, modified, created)
  VALUES ('The University Of Melbourne', 'admin@unimelb.edu.au', 'This is a test institution for this app', '', 1, MD5(CONCAT('admin@unimelb.edu.au', date_trunc('seconds', NOW()))), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW()));

INSERT INTO "user" (institution_id, uid, username, password ,role ,name, email, active, hash, modified, created)
VALUES
  (0, MD5(CONCAT('admin', NOW())), 'admin', MD5(CONCAT('password', MD5('0admin'))), 'admin', 'Administrator', 'admin@example.com', 1, MD5('0admin'), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW()) ),
  (0, MD5(CONCAT('unimelb', NOW())), 'unimelb', MD5(CONCAT('password', MD5('0unimelb'))), 'client', 'Unimelb Client', 'fvas@unimelb.edu.au', 1, MD5('0unimelb'), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW())  ),
  (1, MD5(CONCAT('staff', NOW())), 'staff', MD5(CONCAT('password', MD5('1staff'))), 'staff', 'Unimelb Staff', 'staff@unimelb.edu.au', 1, MD5('1staff'), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW())  ),
  (1, MD5(CONCAT('student', NOW())), 'student', MD5(CONCAT('password', MD5('1student'))), 'student', 'Unimelb Student', 'student@unimelb.edu.au', 1, MD5('1student'), date_trunc('seconds', NOW()) , date_trunc('seconds', NOW())  )
;

INSERT INTO course (institution_id, name, code, email, description, start, finish, active, modified, created)
    VALUES (1, 'Poultry Industry Field Work', 'VETS50001_2014_SM1', 'course@unimelb.edu.au', '',  date_trunc('seconds', NOW()), date_trunc('seconds', (CURRENT_TIMESTAMP + (190 * interval '1 day')) ), 1, date_trunc('seconds', NOW()) , date_trunc('seconds', NOW()) )
;

INSERT INTO user_course (user_id, course_id)
VALUES
  (4, 1)
;

INSERT INTO user_owns_institution (user_id, institution_id)
VALUES
  (2, 1)
;


