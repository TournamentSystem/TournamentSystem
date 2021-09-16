SET FOREIGN_KEY_CHECKS = FALSE;

DROP TABLE IF EXISTS tournament_user;
DROP TABLE IF EXISTS tournament_tournament;
DROP TABLE IF EXISTS tournament_person;
DROP TABLE IF EXISTS tournament_player;
DROP TABLE IF EXISTS tournament_coach;
DROP TABLE IF EXISTS tournament_club;
DROP TABLE IF EXISTS tournament_clubplayer;
DROP TABLE IF EXISTS tournament_team;
DROP TABLE IF EXISTS tournament_teamplayer;
DROP TABLE IF EXISTS tournament_match;
DROP TABLE IF EXISTS tournament_matchplayer;
DROP TABLE IF EXISTS tournament_matchevent;
DROP TABLE IF EXISTS tournament_goal;
DROP TABLE IF EXISTS tournament_substitution;

SET FOREIGN_KEY_CHECKS = TRUE;

DROP PROCEDURE IF EXISTS tournament_matchplayer_teamisplaying_proc;
DROP PROCEDURE IF EXISTS tournament_matchplayer_teamcaptain_proc;
DROP PROCEDURE IF EXISTS tournament_substitution_leavingplayer_proc;

DROP TRIGGER IF EXISTS tournament_matchplayer_insert;
DROP TRIGGER IF EXISTS tournament_matchplayer_update;
DROP TRIGGER IF EXISTS tournament_substitution_insert;
DROP TRIGGER IF EXISTS tournament_substitution_update;


#
# TABLES
#
CREATE TABLE tournament_user (
    name     VARCHAR(32)  NOT NULL,
    password VARCHAR(255) NOT NULL,

    PRIMARY KEY (name)
);

CREATE TABLE tournament_tournament (
    id    INT UNSIGNED NOT NULL,
    name  VARCHAR(64)  NOT NULL,
    start DATETIME     NOT NULL,
    end   DATETIME     NOT NULL,
    owner VARCHAR(32)  NOT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (owner) REFERENCES tournament_user (name)
);

CREATE TABLE tournament_person (
    id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    firstname VARCHAR(128) NOT NULL,
    name      VARCHAR(64)  NOT NULL,
    birthday  DATE         NOT NULL,

    PRIMARY KEY (id)
);

CREATE TABLE tournament_player (
    id INT UNSIGNED NOT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (id) REFERENCES tournament_person (id)
);

CREATE TABLE tournament_coach (
    id INT UNSIGNED NOT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (id) REFERENCES tournament_person (id)
);

CREATE TABLE tournament_club (
    id   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(64)  NOT NULL,

    PRIMARY KEY (id)
);

CREATE TABLE tournament_clubplayer (
    club   INT UNSIGNED NOT NULL,
    player INT UNSIGNED NOT NULL,

    PRIMARY KEY (club, player),
    FOREIGN KEY (club) REFERENCES tournament_club (id),
    FOREIGN KEY (player) REFERENCES tournament_player (id)
);

CREATE TABLE tournament_team (
    club INT UNSIGNED NOT NULL,
    id   INT UNSIGNED NOT NULL /* AUTO_INCREMENT */,
    name VARCHAR(96)  NOT NULL,

    PRIMARY KEY (club, id),
    FOREIGN KEY (club) REFERENCES tournament_club (id)
);

CREATE TABLE tournament_teamplayer (
    club   INT UNSIGNED NOT NULL,
    team   INT UNSIGNED NOT NULL,
    player INT UNSIGNED NOT NULL,
    number TINYINT UNSIGNED DEFAULT NULL,

    PRIMARY KEY (club, team, player),
    FOREIGN KEY (club, team) REFERENCES tournament_team (club, id),
    FOREIGN KEY (club, player) REFERENCES tournament_clubplayer (club, player)
);

CREATE TABLE tournament_match (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    kickoff_time DATETIME     NOT NULL,
    home_club    INT UNSIGNED NOT NULL,
    home_team    INT UNSIGNED NOT NULL,
    guest_club   INT UNSIGNED NOT NULL,
    guest_team   INT UNSIGNED NOT NULL,

    PRIMARY KEY (id),
    FOREIGN KEY (home_club, home_team) REFERENCES tournament_team (club, id),
    FOREIGN KEY (guest_club, guest_team) REFERENCES tournament_team (club, id)
);

CREATE TABLE tournament_matchplayer (
    club         INT UNSIGNED     NOT NULL,
    team         INT UNSIGNED     NOT NULL,
    player       INT UNSIGNED     NOT NULL,
    `match`      INT UNSIGNED     NOT NULL,
    number       TINYINT UNSIGNED NOT NULL,
    team_captain BOOLEAN          NOT NULL DEFAULT FALSE,

    PRIMARY KEY (club, team, player, `match`),
    FOREIGN KEY (club, team, player) REFERENCES tournament_teamplayer (club, team, player),
    FOREIGN KEY (`match`) REFERENCES tournament_match (id),
    UNIQUE (club, team, `match`, number)
);

CREATE TABLE tournament_matchevent (
    `match` INT UNSIGNED     NOT NULL,
    id      INT UNSIGNED     NOT NULL /* AUTO_INCREMENT */,
    minute  TINYINT UNSIGNED NOT NULL,
    club    INT UNSIGNED     NOT NULL,
    team    INT UNSIGNED     NOT NULL,
    player  INT UNSIGNED     NOT NULL,

    PRIMARY KEY (`match`, id),
    FOREIGN KEY (`match`) REFERENCES tournament_match (id),
    FOREIGN KEY (club, team, player, `match`) REFERENCES tournament_matchplayer (club, team, player, `match`)
);

CREATE TABLE tournament_goal (
    `match`  INT UNSIGNED NOT NULL,
    event_id INT UNSIGNED NOT NULL,
    own_goal BOOLEAN      NOT NULL DEFAULT FALSE,

    PRIMARY KEY (`match`, event_id),
    FOREIGN KEY (`match`, event_id) REFERENCES tournament_matchevent (`match`, id)
);

CREATE TABLE tournament_substitution (
    `match`        INT UNSIGNED NOT NULL,
    event_id       INT UNSIGNED NOT NULL,
    leaving_player INT UNSIGNED NOT NULL,

    PRIMARY KEY (`match`, event_id),
    FOREIGN KEY (`match`, event_id) REFERENCES tournament_matchevent (`match`, id)
);


#
# TRIGGERS & PROCEDURES
#
CREATE PROCEDURE tournament_matchplayer_teamisplaying_proc(IN club INT UNSIGNED, IN team INT UNSIGNED, IN `match` INT UNSIGNED)
    READS SQL DATA
BEGIN
    IF (
           SELECT COUNT(1)
           FROM tournament_match m
           WHERE m.id = `match`
             AND ((
                              m.home_club = club
                          AND m.home_team = team
                      ) OR (
                              m.guest_club = club
                          AND m.guest_team = team
                      ))
       ) = 0 THEN
        SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = 'Given team is not playing in this match';
    END IF;
END;

CREATE PROCEDURE tournament_matchplayer_teamcaptain_proc(IN team_captain BOOLEAN, IN club INT UNSIGNED,
                                                         IN team INT UNSIGNED, IN `match` INT UNSIGNED)
    READS SQL DATA
BEGIN
    IF team_captain AND (
                            SELECT COUNT(1)
                            FROM tournament_matchplayer mp
                            WHERE mp.club = club
                              AND mp.team = team
                              AND mp.`match` = `match`
                              AND mp.team_captain = TRUE
                        ) = 1 THEN
        SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = 'Only one player can be the team captain';
    END IF;
END;

CREATE PROCEDURE tournament_substitution_leavingplayer_proc(IN `match` INT UNSIGNED, IN event_id INT UNSIGNED,
                                                            IN leaving_player INT UNSIGNED)
    READS SQL DATA
BEGIN
    IF (
           SELECT COUNT(1)
           FROM tournament_matchevent me
           WHERE me.`match` = `match`
             AND me.id = event_id
             AND EXISTS(
                   SELECT 1
                   FROM tournament_matchplayer mp
                   WHERE mp.`match` = `match`
                     AND mp.club = me.club
                     AND mp.team = me.team
                     AND mp.player = leaving_player
               )
       ) = 0 THEN
        SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = 'Leaving player has to be in the same team';
    END IF;
END;


CREATE TRIGGER tournament_matchplayer_insert
    BEFORE INSERT
    ON tournament_matchplayer
    FOR EACH ROW
BEGIN
    CALL tournament_matchplayer_teamisplaying_proc(new.club, new.team, new.`match`);
    CALL tournament_matchplayer_teamcaptain_proc(new.team_captain, new.club, new.team, new.`match`);
END;

CREATE TRIGGER tournament_matchplayer_update
    BEFORE UPDATE
    ON tournament_matchplayer
    FOR EACH ROW
BEGIN
    CALL tournament_matchplayer_teamisplaying_proc(new.club, new.team, new.`match`);
    CALL tournament_matchplayer_teamcaptain_proc(new.team_captain, new.club, new.team, new.`match`);
END;

CREATE TRIGGER tournament_substitution_insert
    BEFORE INSERT
    ON tournament_substitution
    FOR EACH ROW
BEGIN
    CALL tournament_substitution_leavingplayer_proc(new.`match`, new.event_id, new.leaving_player);
END;

CREATE TRIGGER tournament_substitution_update
    BEFORE UPDATE
    ON tournament_substitution
    FOR EACH ROW
BEGIN
    CALL tournament_substitution_leavingplayer_proc(new.`match`, new.event_id, new.leaving_player);
END;
