/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.11-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: musicLibraryDB
-- ------------------------------------------------------
-- Server version	10.11.11-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `compositions`
--

DROP TABLE IF EXISTS `compositions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `compositions` (
  `catalog_number` varchar(5) NOT NULL COMMENT 'The catalog number is a letter and 3-digit number, for example M101',
  `name` varchar(255) NOT NULL COMMENT 'The title of the composition',
  `description` varchar(2048) DEFAULT NULL COMMENT 'Description of the composition',
  `composer` varchar(255) DEFAULT NULL COMMENT 'The composer of the piece',
  `arranger` varchar(255) DEFAULT NULL COMMENT 'The arranger of the piece',
  `editor` varchar(255) DEFAULT NULL COMMENT 'The editor or lyricist',
  `publisher` varchar(255) DEFAULT NULL COMMENT 'The name of the publishing company',
  `genre` varchar(4) DEFAULT NULL COMMENT 'Which genre is the piece (from the genres table)',
  `ensemble` varchar(4) DEFAULT NULL COMMENT 'Which ensemble plays this piece ',
  `grade` decimal(2,1) unsigned DEFAULT NULL COMMENT 'Grade of difficulty',
  `last_performance_date` date DEFAULT NULL COMMENT 'When the composition was last performed',
  `duration` bigint(20) DEFAULT NULL COMMENT 'Performance duration in seconds',
  `duration_end` datetime DEFAULT NULL COMMENT 'The time the piece ends - to calculate duration',
  `comments` varchar(4096) DEFAULT NULL COMMENT 'Comments about the piece, liner notes',
  `performance_notes` varchar(4096) DEFAULT NULL COMMENT 'Performance notes (how to rehearse it, for example)',
  `storage_location` varchar(255) DEFAULT NULL COMMENT 'Where it is kept (which drawer)',
  `provenance` varchar(255) DEFAULT NULL COMMENT 'How the piece was acquired (P)urchased (R)ented (B)orrowed (D)onated',
  `date_acquired` date DEFAULT NULL COMMENT 'When the piece was acquired',
  `cost` decimal(8,2) DEFAULT NULL COMMENT 'How much did it cost, in dollars and cents',
  `listening_example_link` varchar(255) DEFAULT NULL COMMENT 'A link to a listening example, maybe on YouTube',
  `checked_out` varchar(255) DEFAULT NULL COMMENT 'To whom was this piece lended',
  `paper_size` varchar(4) NOT NULL DEFAULT 'L' COMMENT 'Physical size, from the paper_sizes table',
  `image_path` varchar(2048) DEFAULT NULL COMMENT 'Where a picture (image) of the score resides',
  `windrep_link` text DEFAULT NULL COMMENT 'Where can you this arrangement on the Wind Repertory site windrep.org?',
  `last_inventory_date` date DEFAULT NULL COMMENT 'When was the last time somebody touched this music',
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'When this record in the database was last updated.',
  `enabled` int(11) unsigned NOT NULL DEFAULT 1 COMMENT 'Set greater than 0 if this composition can be played',
  PRIMARY KEY (`catalog_number`),
  KEY `genre` (`genre`),
  KEY `ensemble` (`ensemble`),
  KEY `paper_size` (`paper_size`),
  FULLTEXT KEY `name` (`name`,`description`,`composer`,`arranger`,`comments`),
  CONSTRAINT `compositions_ibfk_1` FOREIGN KEY (`genre`) REFERENCES `genres` (`id_genre`),
  CONSTRAINT `compositions_ibfk_2` FOREIGN KEY (`ensemble`) REFERENCES `ensembles` (`id_ensemble`),
  CONSTRAINT `compositions_ibfk_3` FOREIGN KEY (`paper_size`) REFERENCES `paper_sizes` (`id_paper_size`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='This table keeps compositions.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compositions`
--

LOCK TABLES `compositions` WRITE;
/*!40000 ALTER TABLE `compositions` DISABLE KEYS */;
INSERT INTO `compositions` VALUES ('B001','Awakening of the Lion','Concert piece from 1891 arranged by Fred Lax.','Kontsky','Fred Lax',NULL,'Carl Fischer','B','CB',5.0,'2023-12-05',360,NULL,'Dramatic concert piece depicting the awakening and roar of a lion.','Very challenging work requiring advanced technique and dramatic interpretation.','Cabinet B, Shelf 3','P','2023-06-20',0.00,NULL,'https://bandmusicpdf.org/awakeninglion/','L',NULL,'','2023-12-05','2025-08-10 12:00:00',1);
INSERT INTO `compositions` VALUES ('B002','Norwegian Artists Carnival','Concert piece from 1918 arranged by Safranek.','Svendsen','Safranek',NULL,'Carl Fischer','B','CB',4.0,'2024-03-08',340,NULL,'Brilliant concert piece with Norwegian folk themes and virtuosic writing.','Challenging work requiring good technical ability and ensemble precision.','Cabinet B, Shelf 4','P','2023-11-12',0.00,NULL,'https://bandmusicpdf.org/norwegianartistscarnival/','L',NULL,'','2024-03-08','2025-08-10 12:00:00',1);
INSERT INTO `compositions` VALUES ('C001','William Tell Overture','Famous overture by Rossini, arranged for concert band.','Gioachino Rossini','Various','','Public Domain','T','CB',5.0,'2023-12-15',720,NULL,'One of the most recognizable classical pieces, featuring the famous finale theme known as the Lone Ranger theme.','Very challenging piece requiring excellent technical skills from all sections. Take time with the famous gallop section.','Cabinet B, Shelf 2','P','2023-06-10',0.00,'https://www.youtube.com/watch?v=example3','','L','images/C001_score.jpg','https://www.windrep.org/William_Tell_Overture','2023-12-15','2025-08-09 15:32:05',1);
INSERT INTO `compositions` VALUES ('H001','A Christmas Festival','Medley of popular Christmas carols arranged by Leroy Anderson.','Traditional','Leroy Anderson',NULL,'Mills Music','H','CB',3.0,'2023-12-20',420,NULL,'Beautiful medley featuring Joy to the World, Deck the Halls, Good King Wenceslas, Hark! The Herald Angels Sing, The First Noel, Silent Night, and Jingle Bells.','Balance is crucial in this arrangement. Each carol should be clearly heard and well-articulated.','Cabinet D, Shelf 1','P','2023-10-12',65.00,'https://www.youtube.com/watch?v=example5','','L','images/H001_score.jpg','','2023-12-20','2024-07-20 10:50:00',1);
INSERT INTO `compositions` VALUES ('J001','In the Mood','Popular swing era hit arranged for concert band.','Joe Garland','Various',NULL,'Various Publishers','J','CB',3.5,'2024-03-20',180,NULL,'Glenn Miller\'s signature tune from the swing era, perfectly arranged for concert band.','Emphasize the swing feel and saxophone soli sections. Don\'t rush the tempo.','Cabinet C, Shelf 1','P','2023-09-05',45.00,'https://www.youtube.com/watch?v=example4','','L','images/J001_score.jpg','','2024-03-20','2024-07-20 10:45:00',1);
INSERT INTO `compositions` VALUES ('J002','Bull Frog Blues','Fox trot from 1916 arranged for band.','Brown, Shrigle','F. Henri Klickmann',NULL,'Will Rossiter','J','CB',3.0,'2024-05-20',150,NULL,'Charming early jazz fox trot with characteristic syncopated rhythms.','Emphasize the syncopation and swing feel. Watch tempo - don\'t rush.','Cabinet C, Shelf 2','P','2024-01-20',0.00,NULL,'https://bandmusicpdf.org/bullfrogblues/','L',NULL,'','2024-05-20','2025-08-10 12:00:00',1);
INSERT INTO `compositions` VALUES ('M001','El Capitan','Famous march by John Philip Sousa, composed in 1896. One of the most popular American marches.','John Philip Sousa',NULL,NULL,'Public Domain','M','CB',3.0,'2024-07-04',240,NULL,'El Capitan is a march composed by John Philip Sousa in 1896. The march takes its title from the comic opera of the same name. It is one of Sousa\'s most famous marches and has become a standard in the wind band repertoire.','Pay attention to the syncopated rhythms in the trio section. Keep the tempo steady and marcato throughout.','Cabinet A, Shelf 1','P','2024-01-15',0.00,'https://www.youtube.com/watch?v=example','','L','images/M001_score.jpg','https://www.windrep.org/El_Capitan','2024-07-04','2024-07-20 10:30:00',1);
INSERT INTO `compositions` VALUES ('M002','Stars and Stripes Forever','The official march of the United States, composed by John Philip Sousa in 1896.','John Philip Sousa',NULL,NULL,'Public Domain','M','CB',4.0,'2024-07-04',210,NULL,'Written in 1896, this is Sousa\'s most famous march and was designated as the official march of the United States in 1987.','Famous piccolo solo in the trio. Ensure piccolo is prominent and well-supported by the band.','Cabinet A, Shelf 1','P','2024-01-15',0.00,'https://www.youtube.com/watch?v=example2','','L','images/M002_score.jpg','https://www.windrep.org/Stars_and_Stripes_Forever','2024-07-04','2024-07-20 10:35:00',1);
INSERT INTO `compositions` VALUES ('M003','Der Adler von Lille','German march from 1916, arranged by Ernst von Schmidt-Köthen.','Hermann L. Blankenburg','Ernst von Schmidt-Köthen',NULL,'Edwin F. Kalmus','M','CB',3.5,'2024-06-15',180,NULL,'Traditional German march from the World War I era with strong brass writing.','Strong marcato style throughout. Pay attention to dynamics in the trio section.','Cabinet A, Shelf 2','P','2024-02-10',0.00,NULL,'https://bandmusicpdf.org/adlervonlille/','L',NULL,'','2024-06-15','2025-08-10 12:00:00',1);
INSERT INTO `compositions` VALUES ('M004','On the Square','March by Frank A. Panella from 1916.','Frank A. Panella',NULL,NULL,'Panella Music Co.','M','CB',3.0,'2024-07-15',200,NULL,'Solid traditional march with good educational value for developing bands.','Standard march style. Good for working on march style and articulation.','Cabinet A, Shelf 3','P','2024-03-10',0.00,NULL,'https://bandmusicpdf.org/onsquare/','L',NULL,'','2024-07-15','2025-08-10 12:00:00',1);
INSERT INTO `compositions` VALUES ('M005','Captain Cupid','March and Two-Step by Arthur Pryor from 1908.','Arthur Pryor',NULL,NULL,'Carl Fischer','M','CB',3.5,'2024-05-30',195,NULL,'Energetic march with two-step characteristics, typical of Pryor\'s style.','Balance march precision with the lighter two-step sections.','Cabinet A, Shelf 4','P','2024-02-15',0.00,NULL,'https://bandmusicpdf.org/captaincupid/','L',NULL,'','2024-05-30','2025-08-10 12:00:00',1);
INSERT INTO `compositions` VALUES ('M006','The Charioteer','March from 1911 arranged by Klickmann.','Ashley','Klickmann',NULL,'McKinley','M','CB',3.0,'2024-06-20',175,NULL,'Solid educational march with good technical demands for intermediate bands.','Focus on precision and clean articulation throughout.','Cabinet A, Shelf 5','P','2024-03-01',0.00,NULL,'https://bandmusicpdf.org/charioteer/','L',NULL,'','2024-06-20','2025-08-10 12:00:00',1);
INSERT INTO `compositions` VALUES ('M007','Grenadier Guards','March from 1906 by Frank Hoyt Losey.','Frank Hoyt Losey','Vandersloot',NULL,'','M','CB',4.0,'2024-07-25',210,NULL,'Challenging march with military precision and sophisticated harmonic writing.','Requires advanced technique. Focus on ensemble precision and dynamic control.','Cabinet A, Shelf 6','P','2024-04-05',0.00,NULL,'https://bandmusicpdf.org/grenadierguards/','L',NULL,'','2024-07-25','2025-08-10 12:00:00',1);
INSERT INTO `compositions` VALUES ('M008','Col. Philbrook March','March from 1894 by Robert B. Hall.','Robert B. Hall',NULL,NULL,'Carl Fischer','M','CB',4.0,'2024-04-22',185,NULL,'Classic march by one of America\'s premier march composers of the late 19th century.','Traditional march style with Hall\'s characteristic sophisticated writing.','Cabinet A, Shelf 7','P','2024-01-25',0.00,NULL,'https://bandmusicpdf.org/colphilbrook/','L',NULL,'','2024-04-22','2025-08-10 12:00:00',1);
INSERT INTO `compositions` VALUES ('M009','The Federal March','Historic march from 1788 arranged by Roger Smith.','Alexander Reinagle','Roger Smith',NULL,'JCPenney','M','CB',2.5,'2024-07-04',160,NULL,'Historic American march from the Federal period, newly arranged for modern band.','Simple but dignified. Focus on historical style and clean ensemble playing.','Cabinet A, Shelf 8','P','2024-05-01',0.00,NULL,'https://bandmusicpdf.org/federalmarch/','L',NULL,'','2024-07-04','2025-08-10 12:00:00',1);
INSERT INTO `compositions` VALUES ('M010','Presente','Funeral march by Joan Xamena Sastre.','Joan Xamena Sastre',NULL,NULL,'La Federació Balear de Bandes','M','CB',3.5,'2024-02-20',320,NULL,'Solemn funeral march from Mallorca with dignified and respectful character.','Maintain solemnity throughout. Focus on sustained phrases and dynamic control.','Cabinet A, Shelf 9','P','2023-12-01',0.00,NULL,'https://bandmusicpdf.org/presente/','L',NULL,'','2024-02-20','2025-08-10 12:00:00',1);
INSERT INTO `compositions` VALUES ('M011','Katzenjammer March','March from 1900 by James M. Fulton.','James M. Fulton',NULL,NULL,'J. W. Pepper','M','CB',3.0,'2024-05-05',190,NULL,'Humorous march with playful character, perfect for lighter programs.','Keep the character light and playful. Don\'t take it too seriously!','Cabinet A, Shelf 10','P','2024-02-28',0.00,NULL,'https://bandmusicpdf.org/katzenjammer/','L',NULL,'','2024-05-05','2025-08-10 12:00:00',1);
INSERT INTO `compositions` VALUES ('M012','The Flying Wedge','Galop from 1921 arranged by Richard E. Hildreth.','Kate Dolby','Richard E. Hildreth',NULL,'Walter Jacobs','M','CB',3.0,'2024-06-02',140,NULL,'Fast-paced galop with driving rhythms and energetic character.','Keep the tempo steady and driving. Watch for clean articulation at speed.','Cabinet A, Shelf 11','P','2024-03-15',0.00,NULL,'https://bandmusicpdf.org/flyingwedge-hildreth/','L',NULL,'','2024-06-02','2025-08-10 12:00:00',1);
INSERT INTO `compositions` VALUES ('M013','American Cadet March','March from 1893 by Robert B. Hall.','Robert B. Hall',NULL,NULL,'Harry Coleman','M','CB',3.0,'2024-07-08',205,NULL,'Classic American march with patriotic character and strong melodic content.','Traditional march style. Focus on precision and patriotic character.','Cabinet A, Shelf 12','P','2024-04-20',0.00,NULL,'https://bandmusicpdf.org/americancadet/','L',NULL,'','2024-07-08','2025-08-10 12:00:00',1);
INSERT INTO `compositions` VALUES ('R001','In the Gloaming','Lyrical piece from 1886, arranged by Langstaff.','Harrison','Langstaff',NULL,'Jean White','R','CB',2.5,'2023-11-10',240,NULL,'Beautiful romantic ballad typical of the late Victorian era.','Focus on lyrical phrasing and dynamic contrast. Beautiful solo opportunities.','Cabinet D, Shelf 2','P','2023-08-15',0.00,NULL,'https://bandmusicpdf.org/ingloaming/','L',NULL,'','2023-11-10','2025-08-10 12:00:00',1);
INSERT INTO `compositions` VALUES ('R002','Louisiana Rag','Ragtime piece from 1911 arranged by Alford.','Block','Alford',NULL,'Will Rossiter','R','CB',3.5,'2024-04-12',180,NULL,'Lively ragtime piece with characteristic syncopated melodies.','Keep the ragtime feel light and bouncing. Careful with articulation.','Cabinet C, Shelf 3','P','2024-01-05',0.00,NULL,'https://bandmusicpdf.org/louisianarag/','L',NULL,'','2024-04-12','2025-08-10 12:00:00',1);
INSERT INTO `compositions` VALUES ('R003','That Carolina Rag','Medley from 1911 arranged by Alford.','Hedges Bros, Jacobson, Violinsky','Alford',NULL,'Will Rossiter','R','CB',3.0,'2024-03-25',220,NULL,'Delightful ragtime medley combining several popular themes of the era.','Multiple themes require careful balance and clear transitions between sections.','Cabinet C, Shelf 4','P','2023-12-20',0.00,NULL,'https://bandmusicpdf.org/thatcarolinarag/','L',NULL,'','2024-03-25','2025-08-10 12:00:00',1);
INSERT INTO `compositions` VALUES ('R004','The Voice of Love','Serenade from 1897 arranged by Theo. Moses Tobani.','August Haertel','Theo. Moses Tobani',NULL,'Carl Fischer','R','CB',2.5,'2023-10-30',280,NULL,'Romantic serenade with beautiful melodic writing typical of the late 19th century.','Emphasize the lyrical qualities. Good opportunities for solo work.','Cabinet D, Shelf 3','P','2023-07-12',0.00,NULL,'https://bandmusicpdf.org/voiceoflove/','L',NULL,'','2023-10-30','2025-08-10 12:00:00',1);
INSERT INTO `compositions` VALUES ('R005','Vienna Woodland Violets','Viennese-style waltz with elegant melodies and sophisticated harmonies.','Vollstedt','Kretschmer',NULL,'J. W. Pepper','R','CB',4.0,'2024-02-14',320,NULL,'Viennese-style waltz with elegant melodies and sophisticated harmonies.','Maintain waltz style throughout. Pay attention to phrasing and rubato.','Cabinet D, Shelf 4','P','2023-11-05',0.00,NULL,'https://bandmusicpdf.org/viennawoodland/','L',NULL,'','2024-02-14','2025-08-10 12:00:00',1);
INSERT INTO `compositions` VALUES ('R006','Wabash Blues','American blues piece from 1922.','Meinken',NULL,NULL,'Leo Feist','R','CB',4.0,'2024-01-18',240,NULL,'Early blues piece for band with characteristic blues harmonies and rhythms.','Emphasize the blues style and characteristic bends and slides.','Cabinet C, Shelf 5','P','2023-09-12',0.00,NULL,'https://bandmusicpdf.org/wabashblues/','L',NULL,'','2024-01-18','2025-08-10 12:00:00',1);
INSERT INTO `compositions` VALUES ('R007','Spring Jubilee in the Alps','Dance piece from 1887 arranged by D. W. Reeves.','Joseph Gungl','D. W. Reeves',NULL,'D. W. Reeves','R','CB',3.0,'2024-03-15',280,NULL,'Cheerful Alpine dance with characteristic folk melodies and rhythms.','Capture the folk dance character. Light and buoyant throughout.','Cabinet D, Shelf 5','P','2023-10-18',0.00,NULL,'https://bandmusicpdf.org/springjubilee/','L',NULL,'','2024-03-15','2025-08-10 12:00:00',1);
INSERT INTO `compositions` VALUES ('R008','Alice Where Art Thou','Lyrical piece arranged by Bob Craig.','Joseph Ascher','Bob Craig',NULL,'BandMusic PDF Library','R','CB',3.0,'2023-09-25',200,NULL,'Tender ballad with beautiful melodic lines and gentle accompaniment.','Focus on lyrical expression and careful balance of melody and accompaniment.','Cabinet D, Shelf 6','P','2023-06-08',0.00,NULL,'https://bandmusicpdf.org/alicewhereartthou-craig/','L',NULL,'','2023-09-25','2025-08-10 12:00:00',1);
INSERT INTO `compositions` VALUES ('R009','Anitra\'s Dance','From Peer Gynt Suite arranged by Richard E. Hildreth.','Edvard Grieg','Richard E. Hildreth',NULL,'Walter Jacobs','R','CB',3.5,'2024-04-18',180,NULL,'Exotic dance from Grieg\'s Peer Gynt with characteristic Middle Eastern flavors.','Capture the exotic character. Focus on the unique rhythmic patterns.','Cabinet D, Shelf 7','P','2024-01-14',0.00,NULL,'https://bandmusicpdf.org/anitrasdance/','L',NULL,'','2024-04-18','2025-08-10 12:00:00',1);
INSERT INTO `compositions` VALUES ('W001','Robert le Diable','Selection from Meyerbeer\'s opera arranged by Louis-Philippe Laurendeau.','Giacomo Meyerbeer','Louis-Philippe Laurendeau',NULL,'Carl Fischer','W','CB',4.5,'2024-01-10',420,NULL,'Operatic selection featuring the most memorable themes from Meyerbeer\'s grand opera.','Requires strong technical ability. Focus on operatic style and dramatic expression.','Cabinet E, Shelf 1','P','2023-08-22',0.00,NULL,'https://bandmusicpdf.org/robertdiable-lau-br/','L',NULL,'','2024-01-10','2025-08-10 12:00:00',1);
INSERT INTO `compositions` VALUES ('W002','Die Meistersinger von Nürnberg','Selection from Wagner\'s opera arranged by Theo. Moses Tobani.','Richard Wagner','Theo. Moses Tobani',NULL,'Edwin F. Kalmus','W','CB',5.0,'2023-11-20',480,NULL,'Magnificent selection from Wagner\'s comedy featuring the most famous themes.','Very challenging. Requires advanced ensemble and individual technique.','Cabinet E, Shelf 2','P','2023-07-30',0.00,NULL,'https://bandmusicpdf.org/diemeistersinger/','L',NULL,'','2023-11-20','2025-08-10 12:00:00',1);
/*!40000 ALTER TABLE `compositions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `concerts`
--

DROP TABLE IF EXISTS `concerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `concerts` (
  `id_concert` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Incremented unique number for concert ID',
  `id_playgram` int(11) NOT NULL COMMENT 'Which playgram will be performed',
  `performance_date` date NOT NULL COMMENT 'Date of the concert performance',
  `venue` varchar(255) NOT NULL COMMENT 'Where the concert is held.',
  `conductor` varchar(255) NOT NULL COMMENT 'Optional name of the conductor.',
  `notes` text NOT NULL COMMENT 'Optional performance-specific notes.',
  PRIMARY KEY (`id_concert`),
  UNIQUE KEY `id_playgram` (`id_playgram`,`performance_date`,`venue`),
  CONSTRAINT `concerts_ibfk_1` FOREIGN KEY (`id_playgram`) REFERENCES `playgrams` (`id_playgram`)
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='This table keeps concerts performance data.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `concerts`
--

LOCK TABLES `concerts` WRITE;
/*!40000 ALTER TABLE `concerts` DISABLE KEYS */;
INSERT INTO `concerts` VALUES (1,1,'2024-07-04','Community Center','John Smith','Independence Day celebration concert');
INSERT INTO `concerts` VALUES (2,3,'2023-12-20','First Methodist Church','Mary Johnson','Annual Christmas concert with special guests');
INSERT INTO `concerts` VALUES (3,2,'2024-03-15','High School Auditorium','John Smith','Spring classical concert');
INSERT INTO `concerts` VALUES (4,4,'2024-02-14','Jazz Club','Mike Davis','Valentine\'s Day jazz night');
INSERT INTO `concerts` VALUES (5,5,'2024-06-15','City Park Bandstand','John Smith','Summer march concert in the park');
/*!40000 ALTER TABLE `concerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `download_tokens`
--
CREATE TABLE download_tokens (
  id_download_token INT AUTO_INCREMENT PRIMARY KEY,
  token VARCHAR(64) NOT NULL UNIQUE COMMENT 'The unique download token (use a secure random string).',
  email VARCHAR(255) DEFAULT NULL COMMENT 'Optional: email address to which the token was sent.',
    id_playgram INT(11) NOT NULL COMMENT 'The ID of the playgram this token is for.',
    id_section INT(10) UNSIGNED NOT NULL COMMENT 'The ID of the section this token is for.',
    zip_filename VARCHAR(255) NOT NULL COMMENT 'The name of the ZIP file to serve.',
    expires_at DATETIME NOT NULL COMMENT 'When the token expires.',
    used TINYINT(1) DEFAULT 0 COMMENT '0 = not used, 1 = used.',
    id_user INT(10) UNSIGNED DEFAULT NULL COMMENT 'The user ID of the librarian who created the token.',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'When the token was created.',
    INDEX (token),
    INDEX (id_playgram),
    INDEX (id_section),
    INDEX (id_user),
    CONSTRAINT fk_download_tokens_playgram
        FOREIGN KEY (id_playgram) REFERENCES playgrams(id_playgram)
        ON DELETE CASCADE,
    CONSTRAINT fk_download_tokens_section
        FOREIGN KEY (id_section) REFERENCES sections(id_section)
        ON DELETE CASCADE,
    CONSTRAINT fk_download_tokens_user
        FOREIGN KEY (id_user) REFERENCES users(id_users)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Tokens for limited-use section ZIP downloads.';

--
--
-- Table structure for table `ensembles`
--

DROP TABLE IF EXISTS `ensembles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ensembles` (
  `id_ensemble` varchar(4) NOT NULL COMMENT 'The unique ID of this ensemble (one letter)',
  `name` varchar(255) NOT NULL COMMENT 'The name of the ensemble',
  `description` varchar(512) DEFAULT NULL COMMENT 'A description of the ensemble',
  `link` varchar(512) DEFAULT NULL COMMENT 'Hypertext link to more about this ensemble',
  `enabled` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Set non-zero to enable the ensemble to be used',
  PRIMARY KEY (`id_ensemble`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='This table keeps ensembles (performing groups).';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ensembles`
--

LOCK TABLES `ensembles` WRITE;
/*!40000 ALTER TABLE `ensembles` DISABLE KEYS */;
INSERT INTO `ensembles` VALUES ('BO','Brass Orchestra','Brass instruments only','',1);
INSERT INTO `ensembles` VALUES ('BQ','Brass Quintet','Traditional brass quintet','',1);
INSERT INTO `ensembles` VALUES ('CB','Concert Band','Full concert band with woodwinds, brass, and percussion','',1);
INSERT INTO `ensembles` VALUES ('MB','Marching Band','Marching band formation','',1);
INSERT INTO `ensembles` VALUES ('O','Orchestra','Full symphony orchestra with strings','',1);
INSERT INTO `ensembles` VALUES ('PQ','Percussion Quartet','Four percussion players','',1);
INSERT INTO `ensembles` VALUES ('S','String Orchestra','String instruments only','',1);
INSERT INTO `ensembles` VALUES ('WE','Wind Ensemble','Smaller wind ensemble with one player per part','',1);
INSERT INTO `ensembles` VALUES ('WO','Woodwind Orchestra','Woodwind instruments only','',1);
INSERT INTO `ensembles` VALUES ('WQ','Woodwind Quintet','Traditional woodwind quintet','',1);
/*!40000 ALTER TABLE `ensembles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `genres`
--

DROP TABLE IF EXISTS `genres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `genres` (
  `id_genre` varchar(4) NOT NULL COMMENT 'The unique ID of this genre (1-4 letters)',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT 'Name of the genre, for example March',
  `description` varchar(255) DEFAULT '' COMMENT 'Description or comments of this particular genre',
  `enabled` int(11) unsigned NOT NULL DEFAULT 0 COMMENT 'Set non-zero to enable the genre to be used',
  PRIMARY KEY (`id_genre`),
  UNIQUE KEY `id_genre` (`id_genre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='This table keeps genres (March, Jazz, Transcription, etc.).';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `genres`
--

LOCK TABLES `genres` WRITE;
/*!40000 ALTER TABLE `genres` DISABLE KEYS */;
INSERT INTO `genres` VALUES ('B','Original works for Band','Includes pieces that are not symphonic transcriptions or operatic settings. Includes Suites, Overtures, Chorales, and Fugues.',1);
INSERT INTO `genres` VALUES ('C','Ceremonial music','Materials specifically designed for functions. Includes fanfares, processionals, national anthems and patriotic medleys, hymns and chorales for memorials.',1);
INSERT INTO `genres` VALUES ('CM','Circus march','Fast tempo marches',1);
INSERT INTO `genres` VALUES ('H','Holiday','Holiday and seasonal music',1);
INSERT INTO `genres` VALUES ('J','Jazz and swing','Jazz arrangements and original jazz compositions',1);
INSERT INTO `genres` VALUES ('M','Military march','Traditional military and ceremonial marches',1);
INSERT INTO `genres` VALUES ('O','Other','Something that doesn\'t fit another genre',1);
INSERT INTO `genres` VALUES ('P','Pop and rock','Arrangements of popular music (Beatles, Queen, ABBA, Disney medleys)',1);
INSERT INTO `genres` VALUES ('R','Programmatic music','Descriptive music that depicts historical events, landscapes and nature, folk song arrangements, or story-based works.',1);
INSERT INTO `genres` VALUES ('S','Solo with band accompaniment','Piece for solo instrument with band accompaniment',1);
INSERT INTO `genres` VALUES ('T','Transcription',' 	Transcriptions of classic and contemporary symphonic works for band',1);
INSERT INTO `genres` VALUES ('U','Unknown','Use for compositions that have not been cataloged',1);
INSERT INTO `genres` VALUES ('W','Show tunes','Music from plays or Broadway shows',1);
/*!40000 ALTER TABLE `genres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `instruments`
--

DROP TABLE IF EXISTS `instruments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `instruments` (
  `id_instrument` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'The ID of this instrument.',
  `collation` int(10) unsigned NOT NULL COMMENT 'Orchestra score order',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT 'The name of the instrument, for example Trumpet',
  `description` varchar(2048) DEFAULT NULL COMMENT 'Longer description of the instrument',
  `family` varchar(128) NOT NULL COMMENT 'Woodwind, brass, percussion, strings, etc.',
  `enabled` int(11) unsigned NOT NULL DEFAULT 0 COMMENT 'Set to 1 to enable this instrument',
  PRIMARY KEY (`id_instrument`)
) ENGINE=InnoDB AUTO_INCREMENT=251 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='This table holds names of instruments to use in parts.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `instruments`
--

LOCK TABLES `instruments` WRITE;
/*!40000 ALTER TABLE `instruments` DISABLE KEYS */;
INSERT INTO `instruments` VALUES (1,10,'Piccolo','Small flute pitched an octave higher','Woodwind',1);
INSERT INTO `instruments` VALUES (2,20,'Flute','Standard concert flute','Woodwind',1);
INSERT INTO `instruments` VALUES (3,30,'Oboe','Double reed woodwind instrument','Woodwind',1);
INSERT INTO `instruments` VALUES (4,35,'English Horn','Alto oboe','Woodwind',1);
INSERT INTO `instruments` VALUES (5,40,'Bassoon','Large double reed instrument','Woodwind',1);
INSERT INTO `instruments` VALUES (6,50,'Eb Clarinet','Small high-pitched clarinet','Woodwind',1);
INSERT INTO `instruments` VALUES (7,60,'Bb Clarinet','Standard clarinet','Woodwind',1);
INSERT INTO `instruments` VALUES (8,70,'Bass Clarinet','Large low clarinet','Woodwind',1);
INSERT INTO `instruments` VALUES (9,80,'Alto Saxophone','Alto saxophone in Eb','Woodwind',1);
INSERT INTO `instruments` VALUES (10,90,'Tenor Saxophone','Tenor saxophone in Bb','Woodwind',1);
INSERT INTO `instruments` VALUES (11,100,'Baritone Saxophone','Baritone saxophone in Eb','Woodwind',1);
INSERT INTO `instruments` VALUES (12,110,'Trumpet','Standard Bb trumpet','Brass',1);
INSERT INTO `instruments` VALUES (13,115,'Cornet','Bb cornet','Brass',1);
INSERT INTO `instruments` VALUES (14,120,'Flugelhorn','Bb flugelhorn','Brass',1);
INSERT INTO `instruments` VALUES (15,130,'French Horn','F/Bb French horn','Brass',1);
INSERT INTO `instruments` VALUES (16,140,'Trombone','Tenor trombone','Brass',1);
INSERT INTO `instruments` VALUES (17,150,'Bass Trombone','Large bass trombone','Brass',1);
INSERT INTO `instruments` VALUES (18,160,'Euphonium','Baritone horn/euphonium','Brass',1);
INSERT INTO `instruments` VALUES (19,170,'Tuba','Bass tuba','Brass',1);
INSERT INTO `instruments` VALUES (20,180,'Timpani','Kettle drums','Percussion',1);
INSERT INTO `instruments` VALUES (21,190,'Snare Drum','Side drum','Percussion',1);
INSERT INTO `instruments` VALUES (22,200,'Bass Drum','Large bass drum','Percussion',1);
INSERT INTO `instruments` VALUES (23,210,'Crash Cymbals','Orchestral crash cymbals','Percussion',1);
INSERT INTO `instruments` VALUES (24,220,'Suspended Cymbal','Single suspended cymbal','Percussion',1);
INSERT INTO `instruments` VALUES (25,230,'Triangle','Metal triangle','Percussion',1);
INSERT INTO `instruments` VALUES (26,240,'Tambourine','Frame drum with jingles','Percussion',1);
INSERT INTO `instruments` VALUES (27,250,'Glockenspiel','Orchestral bells','Percussion',1);
INSERT INTO `instruments` VALUES (28,260,'Xylophone','Wooden keyboard percussion','Percussion',1);
INSERT INTO `instruments` VALUES (29,270,'Vibraphone','Metal keyboard with motor','Percussion',1);
INSERT INTO `instruments` VALUES (30,280,'Marimba','Large wooden keyboard percussion','Percussion',1);
INSERT INTO `instruments` VALUES (250,155,'Baritone horn','Similar to a euphonium, but typically bell-forward design','Brass',1);
/*!40000 ALTER TABLE `instruments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paper_sizes`
--

DROP TABLE IF EXISTS `paper_sizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `paper_sizes` (
  `id_paper_size` varchar(4) NOT NULL COMMENT 'Paper size ID (one letter)',
  `name` varchar(255) NOT NULL COMMENT 'Size, for example Legal, Letter, Folio',
  `description` varchar(255) DEFAULT NULL COMMENT 'Use to list other examples',
  `vertical` decimal(7,2) unsigned DEFAULT NULL COMMENT 'Vertical size in inches',
  `horizontal` decimal(7,2) unsigned DEFAULT NULL COMMENT 'Horizontal size in inches',
  `enabled` int(11) unsigned NOT NULL DEFAULT 0 COMMENT 'Set greater than 0 if this size is used',
  PRIMARY KEY (`id_paper_size`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='This table keeps paper sizes.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paper_sizes`
--

LOCK TABLES `paper_sizes` WRITE;
/*!40000 ALTER TABLE `paper_sizes` DISABLE KEYS */;
INSERT INTO `paper_sizes` VALUES ('A','A4','International A4 size',11.69,8.27,1);
INSERT INTO `paper_sizes` VALUES ('B','Book','Standard book size',10.00,7.00,1);
INSERT INTO `paper_sizes` VALUES ('F','Folio','Large folio size',17.00,11.00,1);
INSERT INTO `paper_sizes` VALUES ('G','Legal','8.5 x 14 inches',14.00,8.50,1);
INSERT INTO `paper_sizes` VALUES ('L','Letter','8.5 x 11 inches',11.00,8.50,1);
INSERT INTO `paper_sizes` VALUES ('M','March','Small march card size',9.00,6.00,1);
INSERT INTO `paper_sizes` VALUES ('T','Tabloid','11 x 17 inches',17.00,11.00,1);
/*!40000 ALTER TABLE `paper_sizes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `part_collections`
--

DROP TABLE IF EXISTS `part_collections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `part_collections` (
  `catalog_number_key` varchar(255) NOT NULL COMMENT 'Catalog number of the part ID',
  `id_part_type_key` int(10) unsigned NOT NULL COMMENT 'Part ID that this collection belongs to',
  `id_instrument_key` int(10) unsigned NOT NULL COMMENT 'Which instrument type is part of this collection',
  `name` varchar(255) DEFAULT NULL COMMENT 'The name of the type of part, for example Percussion 1',
  `description` varchar(255) DEFAULT NULL COMMENT 'Complete description of this part collection',
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'When this record in the database was last updated.',
  KEY `catalog_number_key` (`catalog_number_key`),
  KEY `id_part_type_key` (`id_part_type_key`),
  KEY `id_instrument_key` (`id_instrument_key`) USING BTREE,
  KEY `fk_parts` (`catalog_number_key`,`id_part_type_key`),
  CONSTRAINT `fk_instruments_parts` FOREIGN KEY (`id_instrument_key`) REFERENCES `instruments` (`id_instrument`),
  CONSTRAINT `fk_parts` FOREIGN KEY (`catalog_number_key`, `id_part_type_key`) REFERENCES `parts` (`catalog_number`, `id_part_type`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='This table holds part collections.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `part_collections`
--

LOCK TABLES `part_collections` WRITE;
/*!40000 ALTER TABLE `part_collections` DISABLE KEYS */;
INSERT INTO `part_collections` VALUES ('R007',1,1,'Piccolo',NULL,'2025-08-10 19:37:01');
INSERT INTO `part_collections` VALUES ('R007',241,3,'Oboe',NULL,'2025-08-10 19:39:28');
INSERT INTO `part_collections` VALUES ('R007',242,5,'Bassoon',NULL,'2025-08-10 19:39:34');
INSERT INTO `part_collections` VALUES ('R007',10,7,'1st Bb Clarinet',NULL,'2025-08-10 19:39:45');
INSERT INTO `part_collections` VALUES ('R007',232,7,'2d & 3d Bb Clarinets',NULL,'2025-08-10 19:40:06');
INSERT INTO `part_collections` VALUES ('R007',232,7,'2d & 3d Bb Clarinets',NULL,'2025-08-10 19:40:06');
INSERT INTO `part_collections` VALUES ('R007',14,9,'1st Alto Eb',NULL,'2025-08-10 19:40:26');
INSERT INTO `part_collections` VALUES ('R007',30,19,'Bass Tuba',NULL,'2025-08-10 19:40:50');
INSERT INTO `part_collections` VALUES ('R007',240,18,'Baritone',NULL,'2025-08-10 19:40:59');
INSERT INTO `part_collections` VALUES ('R007',239,250,'Baritone Treble Clef',NULL,'2025-08-10 19:41:12');
INSERT INTO `part_collections` VALUES ('R007',28,17,'Bass Trombone',NULL,'2025-08-10 19:41:26');
INSERT INTO `part_collections` VALUES ('R007',238,16,'1st & 2d Trombones',NULL,'2025-08-10 19:41:43');
INSERT INTO `part_collections` VALUES ('R007',238,16,'1st & 2d Trombones',NULL,'2025-08-10 19:41:43');
INSERT INTO `part_collections` VALUES ('R007',235,13,'2d & 3d Cornets',NULL,'2025-08-10 19:42:01');
INSERT INTO `part_collections` VALUES ('R007',235,13,'2d & 3d Cornets',NULL,'2025-08-10 19:42:01');
INSERT INTO `part_collections` VALUES ('R007',233,13,'1st Bb Cornet',NULL,'2025-08-10 19:42:13');
INSERT INTO `part_collections` VALUES ('R007',237,9,'2d & 4th Altos',NULL,'2025-08-10 19:42:29');
INSERT INTO `part_collections` VALUES ('R007',237,9,'2d & 4th Altos',NULL,'2025-08-10 19:42:29');
INSERT INTO `part_collections` VALUES ('R007',15,9,'2d Alto Eb',NULL,'2025-08-10 19:42:42');
/*!40000 ALTER TABLE `part_collections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `part_types`
--

DROP TABLE IF EXISTS `part_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `part_types` (
  `id_part_type` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'The ID of this part type.',
  `collation` int(10) unsigned NOT NULL COMMENT 'Orchestra score order',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT 'The name of the type of part, for example Trumpet 1',
  `description` varchar(2048) DEFAULT NULL COMMENT 'Longer description of the type of part',
  `family` varchar(128) NOT NULL COMMENT 'Woodwind, brass, percussion, strings, etc.',
  `default_instrument` int(10) unsigned DEFAULT NULL COMMENT 'The default instrument for this part, from the instruments table',
  `is_part_collection` int(10) unsigned DEFAULT NULL COMMENT 'If this part is more than one instrument this is the ID of the collection',
  `enabled` int(11) unsigned NOT NULL DEFAULT 0 COMMENT 'Set to 1 to enable this part type',
  PRIMARY KEY (`id_part_type`),
  KEY `fk_instruments` (`default_instrument`),
  CONSTRAINT `fk_instruments` FOREIGN KEY (`default_instrument`) REFERENCES `instruments` (`id_instrument`)
) ENGINE=InnoDB AUTO_INCREMENT=245 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='This table holds kinds/types of parts for parts and part collections.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `part_types`
--

LOCK TABLES `part_types` WRITE;
/*!40000 ALTER TABLE `part_types` DISABLE KEYS */;
INSERT INTO `part_types` VALUES (1,10,'Piccolo','Solo piccolo part','Woodwind',1,NULL,1);
INSERT INTO `part_types` VALUES (2,30,'Flute 1','First flute part','Woodwind',2,NULL,1);
INSERT INTO `part_types` VALUES (3,40,'Flute 2','Second flute part','Woodwind',2,NULL,1);
INSERT INTO `part_types` VALUES (4,60,'Oboe 1','First oboe part','Woodwind',3,NULL,1);
INSERT INTO `part_types` VALUES (5,70,'Oboe 2','Second oboe part','Woodwind',3,NULL,1);
INSERT INTO `part_types` VALUES (6,80,'English Horn','English horn part','Woodwind',4,NULL,1);
INSERT INTO `part_types` VALUES (7,100,'Bassoon 1','First bassoon part','Woodwind',5,NULL,1);
INSERT INTO `part_types` VALUES (8,110,'Bassoon 2','Second bassoon part','Woodwind',5,NULL,1);
INSERT INTO `part_types` VALUES (9,120,'Eb Clarinet','Eb clarinet part','Woodwind',6,NULL,1);
INSERT INTO `part_types` VALUES (10,130,'Bb Clarinet 1','First Bb clarinet part','Woodwind',7,NULL,1);
INSERT INTO `part_types` VALUES (11,140,'Bb Clarinet 2','Second Bb clarinet part','Woodwind',7,NULL,1);
INSERT INTO `part_types` VALUES (12,160,'Bb Clarinet 3','Third Bb clarinet part','Woodwind',7,NULL,1);
INSERT INTO `part_types` VALUES (13,170,'Bass Clarinet','Bass clarinet part','Woodwind',8,NULL,1);
INSERT INTO `part_types` VALUES (14,180,'Alto Saxophone 1','First alto saxophone part','Woodwind',9,NULL,1);
INSERT INTO `part_types` VALUES (15,190,'Alto Saxophone 2','Second alto saxophone part','Woodwind',9,NULL,1);
INSERT INTO `part_types` VALUES (16,210,'Tenor Saxophone','Tenor saxophone part','Woodwind',10,NULL,1);
INSERT INTO `part_types` VALUES (17,220,'Baritone Saxophone','Baritone saxophone part','Woodwind',11,NULL,1);
INSERT INTO `part_types` VALUES (18,280,'Trumpet 1','First trumpet part','Brass',12,NULL,1);
INSERT INTO `part_types` VALUES (19,290,'Trumpet 2','Second trumpet part','Brass',12,NULL,1);
INSERT INTO `part_types` VALUES (20,300,'Trumpet 3','Third trumpet part','Brass',12,NULL,1);
INSERT INTO `part_types` VALUES (21,310,'French Horn 1','First French horn part','Brass',15,NULL,1);
INSERT INTO `part_types` VALUES (22,320,'French Horn 2','Second French horn part','Brass',15,NULL,1);
INSERT INTO `part_types` VALUES (23,330,'French Horn 3','Third French horn part','Brass',15,NULL,1);
INSERT INTO `part_types` VALUES (24,340,'French Horn 4','Fourth French horn part','Brass',15,NULL,1);
INSERT INTO `part_types` VALUES (25,350,'Trombone 1','First trombone part','Brass',16,NULL,1);
INSERT INTO `part_types` VALUES (26,370,'Trombone 2','Second trombone part','Brass',16,NULL,1);
INSERT INTO `part_types` VALUES (27,380,'Trombone 3','Third trombone part','Brass',16,NULL,1);
INSERT INTO `part_types` VALUES (28,390,'Bass Trombone','Bass trombone part','Brass',17,NULL,1);
INSERT INTO `part_types` VALUES (29,420,'Euphonium','Euphonium/baritone part','Brass',18,NULL,1);
INSERT INTO `part_types` VALUES (30,430,'Tuba','Tuba part','Brass',19,NULL,1);
INSERT INTO `part_types` VALUES (31,440,'Timpani','Timpani part','Percussion',20,NULL,1);
INSERT INTO `part_types` VALUES (32,450,'Percussion 1','First percussion part','Percussion',21,NULL,1);
INSERT INTO `part_types` VALUES (33,460,'Percussion 2','Second percussion part','Percussion',22,NULL,1);
INSERT INTO `part_types` VALUES (34,470,'Percussion 3','Third percussion part','Percussion',23,NULL,1);
INSERT INTO `part_types` VALUES (35,480,'Percussion 4','Fourth percussion part','Percussion',24,NULL,1);
INSERT INTO `part_types` VALUES (231,230,'Solo Cornet','Cornet part separate from the 1st cornet','Brass',13,NULL,1);
INSERT INTO `part_types` VALUES (232,150,'Bb Clarinet 2 & 3','Combine 2nd and 3rd clarinets','Woodwind',7,1,1);
INSERT INTO `part_types` VALUES (233,240,'Cornet 1','First cornet','Brass',13,NULL,1);
INSERT INTO `part_types` VALUES (234,250,'Cornet 2','2nd Cornet','Brass',13,NULL,1);
INSERT INTO `part_types` VALUES (235,260,'Cornet 2 & 3','Combined 2nd and 3rd cornets','Brass',13,1,1);
INSERT INTO `part_types` VALUES (236,270,'Cornet 3','3rd Cornet','Brass',13,NULL,1);
INSERT INTO `part_types` VALUES (237,200,'Alto Saxophone 3 & 4','3rd and 4th Alto Saxophone','Woodwind',9,1,1);
INSERT INTO `part_types` VALUES (238,360,'Trombone 1 & 2','1st and 2nd Trombone','Brass',16,1,1);
INSERT INTO `part_types` VALUES (239,400,'Baritone TC','Baritone (or euphonium) that reads treble clef','Brass',250,NULL,1);
INSERT INTO `part_types` VALUES (240,410,'Baritone BC','Same as euphonium, but named this way in early band music.','Brass',18,NULL,1);
INSERT INTO `part_types` VALUES (241,50,'Oboe','The only oboe part','Woodwind',3,NULL,1);
INSERT INTO `part_types` VALUES (242,90,'Bassoon','The only bassoon part.','Woodwind',5,NULL,1);
INSERT INTO `part_types` VALUES (243,20,'Flute','The only flute part','Woodwind',2,NULL,1);
INSERT INTO `part_types` VALUES (244,1,'Full Score','Conductor\'s score','Other',1,NULL,1);
/*!40000 ALTER TABLE `part_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parts`
--

DROP TABLE IF EXISTS `parts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `parts` (
  `catalog_number` varchar(255) NOT NULL DEFAULT '' COMMENT 'Library catalog number of the composition to which this part belongs',
  `id_part_type` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Which type of part, from the part_types table',
  `name` varchar(255) DEFAULT '' COMMENT 'Name of the part, if different from the part type',
  `description` varchar(255) DEFAULT '' COMMENT 'Description or comments of this particular part',
  `is_part_collection` int(11) DEFAULT NULL COMMENT 'This is a part collection of other parts',
  `paper_size` varchar(4) DEFAULT NULL COMMENT 'Physical size, from the paper_sizes table',
  `page_count` int(11) DEFAULT NULL COMMENT 'How many pages does this part contain?',
  `image_path` text DEFAULT NULL COMMENT 'Where an image of this part is stored.',
  `originals_count` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Set greater than 0 if originals of this part exist',
  `copies_count` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Set greater than 0 if copies of this part exist',
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'When this record in the database was last updated.',
  PRIMARY KEY (`catalog_number`,`id_part_type`),
  KEY `id_part_type` (`id_part_type`),
  KEY `paper_size` (`paper_size`),
  KEY `catalog_number` (`catalog_number`),
  CONSTRAINT `parts_ibfk_1` FOREIGN KEY (`id_part_type`) REFERENCES `part_types` (`id_part_type`),
  CONSTRAINT `parts_ibfk_2` FOREIGN KEY (`paper_size`) REFERENCES `paper_sizes` (`id_paper_size`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `parts_ibfk_3` FOREIGN KEY (`catalog_number`) REFERENCES `compositions` (`catalog_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='This table holds parts.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parts`
--

LOCK TABLES `parts` WRITE;
/*!40000 ALTER TABLE `parts` DISABLE KEYS */;
INSERT INTO `parts` VALUES ('C001',2,'Flute 1','Technical passages',2,'L',3,NULL,2,3,'2024-07-20 10:40:00');
INSERT INTO `parts` VALUES ('C001',10,'Bb Clarinet 1',NULL,NULL,'L',3,'',3,5,'2024-07-20 10:40:00');
INSERT INTO `parts` VALUES ('C001',18,'Trumpet 1','Famous gallop section',2,'L',3,NULL,2,3,'2024-07-20 10:40:00');
INSERT INTO `parts` VALUES ('C001',25,'Trombone 1',NULL,NULL,'L',3,'',1,2,'2024-07-20 10:40:00');
INSERT INTO `parts` VALUES ('C001',31,'Timpani','Important role throughout',2,'L',3,NULL,1,1,'2024-07-20 10:40:00');
INSERT INTO `parts` VALUES ('H001',2,'Flute 1','Melodic lines in carols',2,'L',2,NULL,2,4,'2024-07-20 10:50:00');
INSERT INTO `parts` VALUES ('H001',10,'Bb Clarinet 1',NULL,NULL,'L',2,NULL,3,6,'2024-07-20 10:50:00');
INSERT INTO `parts` VALUES ('H001',18,'Trumpet 1','Fanfare sections',2,'L',2,NULL,2,4,'2024-07-20 10:50:00');
INSERT INTO `parts` VALUES ('H001',21,'French Horn 1','Important harmonic role',2,'L',2,NULL,1,2,'2024-07-20 10:50:00');
INSERT INTO `parts` VALUES ('H001',31,'Timpani',NULL,NULL,'L',2,NULL,1,1,'2024-07-20 10:50:00');
INSERT INTO `parts` VALUES ('J001',1,'Piccolo','Featured in the opening section',2,'L',2,NULL,1,2,'2024-07-20 10:45:00');
INSERT INTO `parts` VALUES ('J001',2,'Flute 1','Melodic lines in the intro',2,'L',2,NULL,2,3,'2024-07-20 10:45:00');
INSERT INTO `parts` VALUES ('J001',10,'Bb Clarinet 1','Swing style',2,'L',2,NULL,3,5,'2024-07-20 10:45:00');
INSERT INTO `parts` VALUES ('J001',14,'Alto Saxophone 1','Featured soli section',2,'L',2,NULL,1,2,'2024-07-20 10:45:00');
INSERT INTO `parts` VALUES ('J001',18,'Trumpet 1','Swing style',2,'L',2,NULL,2,3,'2024-07-20 10:45:00');
INSERT INTO `parts` VALUES ('J001',25,'Trombone 1',NULL,NULL,'L',2,NULL,1,2,'2024-07-20 10:45:00');
INSERT INTO `parts` VALUES ('J001',32,'Percussion 1','Swing drums',2,'L',2,NULL,1,1,'2024-07-20 10:45:00');
INSERT INTO `parts` VALUES ('M001',1,'Piccolo',NULL,NULL,'L',2,NULL,1,3,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',2,'Flute 1',NULL,NULL,'L',2,NULL,2,4,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',3,'Flute 2',NULL,NULL,'L',2,NULL,1,3,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',4,'Oboe 1',NULL,NULL,'L',2,NULL,1,2,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',5,'Oboe 2',NULL,NULL,'L',2,NULL,1,1,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',9,'Eb Clarinet',NULL,NULL,'L',2,NULL,1,1,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',10,'Bb Clarinet 1',NULL,NULL,'L',2,NULL,3,6,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',11,'Bb Clarinet 2',NULL,NULL,'L',2,NULL,2,8,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',12,'Bb Clarinet 3',NULL,NULL,'L',2,NULL,2,6,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',13,'Bass Clarinet',NULL,NULL,'L',2,NULL,1,2,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',14,'Alto Saxophone 1',NULL,NULL,'L',2,NULL,1,2,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',15,'Alto Saxophone 2',NULL,NULL,'L',2,NULL,1,1,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',16,'Tenor Saxophone',NULL,NULL,'L',2,NULL,1,1,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',17,'Baritone Saxophone',NULL,NULL,'L',2,NULL,1,1,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',18,'Trumpet 1',NULL,NULL,'L',2,NULL,2,4,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',19,'Trumpet 2',NULL,NULL,'L',2,NULL,2,3,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',20,'Trumpet 3',NULL,NULL,'L',2,NULL,1,2,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',21,'French Horn 1',NULL,NULL,'L',2,NULL,1,2,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',22,'French Horn 2',NULL,NULL,'L',2,NULL,1,1,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',25,'Trombone 1',NULL,NULL,'L',2,NULL,1,2,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',26,'Trombone 2',NULL,NULL,'L',2,NULL,1,2,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',27,'Trombone 3',NULL,NULL,'L',2,NULL,1,1,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',29,'Euphonium',NULL,NULL,'L',2,NULL,1,2,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',30,'Tuba',NULL,NULL,'L',2,NULL,1,2,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',31,'Timpani',NULL,NULL,'L',2,NULL,1,1,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',32,'Percussion 1','Snare Drum, Bass Drum',2,'L',2,NULL,1,1,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M001',33,'Percussion 2','Cymbals, Triangle',2,'L',2,NULL,1,1,'2024-07-20 10:30:00');
INSERT INTO `parts` VALUES ('M002',1,'Piccolo','Famous solo in trio',2,'L',2,NULL,1,2,'2024-07-20 10:35:00');
INSERT INTO `parts` VALUES ('M002',2,'Flute 1',NULL,NULL,'L',2,NULL,2,4,'2024-07-20 10:35:00');
INSERT INTO `parts` VALUES ('M002',10,'Bb Clarinet 1',NULL,NULL,'L',2,NULL,3,6,'2024-07-20 10:35:00');
INSERT INTO `parts` VALUES ('M002',18,'Trumpet 1',NULL,NULL,'L',2,NULL,2,4,'2024-07-20 10:35:00');
INSERT INTO `parts` VALUES ('M002',25,'Trombone 1',NULL,NULL,'L',2,NULL,1,2,'2024-07-20 10:35:00');
INSERT INTO `parts` VALUES ('M002',30,'Tuba',NULL,NULL,'L',2,NULL,1,2,'2024-07-20 10:35:00');
INSERT INTO `parts` VALUES ('M002',31,'Timpani',NULL,NULL,'L',2,NULL,1,1,'2024-07-20 10:35:00');
INSERT INTO `parts` VALUES ('M002',32,'Percussion 1',NULL,NULL,'L',2,NULL,1,1,'2024-07-20 10:35:00');
INSERT INTO `parts` VALUES ('R007',1,'Piccolo',NULL,NULL,'L',1,'627d9c70.pdf',1,0,'2025-08-10 19:37:01');
INSERT INTO `parts` VALUES ('R007',10,'1st Bb Clarinet',NULL,NULL,'L',1,'9ddc550c.pdf',1,0,'2025-08-10 19:39:45');
INSERT INTO `parts` VALUES ('R007',14,'1st Alto Eb',NULL,NULL,'L',1,'9fd7a900.pdf',1,0,'2025-08-10 19:40:26');
INSERT INTO `parts` VALUES ('R007',15,'2d Alto Eb',NULL,NULL,'L',1,'3bd2fecc.pdf',1,0,'2025-08-10 19:42:42');
INSERT INTO `parts` VALUES ('R007',28,'Bass Trombone',NULL,NULL,'L',1,'3ed8d67d.pdf',1,0,'2025-08-10 19:41:26');
INSERT INTO `parts` VALUES ('R007',30,'Bass Tuba',NULL,NULL,'L',1,'85c30cf4.pdf',1,0,'2025-08-10 19:40:50');
INSERT INTO `parts` VALUES ('R007',231,'Solo Cornet',NULL,NULL,'L',1,'df1a7627.pdf',1,0,'2025-08-10 19:36:40');
INSERT INTO `parts` VALUES ('R007',232,'2d & 3d Bb Clarinets',NULL,NULL,'L',1,'2c7683e7.pdf',1,0,'2025-08-10 19:40:06');
INSERT INTO `parts` VALUES ('R007',233,'1st Bb Cornet',NULL,NULL,'L',1,'3b0bdea6.pdf',1,0,'2025-08-10 19:42:13');
INSERT INTO `parts` VALUES ('R007',235,'2d & 3d Cornets',NULL,NULL,'L',1,'87938bec.pdf',1,0,'2025-08-10 19:42:01');
INSERT INTO `parts` VALUES ('R007',237,'2d & 4th Altos',NULL,NULL,'L',1,'f4fa3a92.pdf',1,0,'2025-08-10 19:42:29');
INSERT INTO `parts` VALUES ('R007',238,'1st & 2d Trombones',NULL,NULL,'L',1,'7553ecb1.pdf',1,0,'2025-08-10 19:41:43');
INSERT INTO `parts` VALUES ('R007',239,'Baritone Treble Clef',NULL,NULL,'L',1,'6b70ebb9.pdf',1,0,'2025-08-10 19:41:12');
INSERT INTO `parts` VALUES ('R007',240,'Baritone',NULL,NULL,'L',1,'7d6d85fb.pdf',1,0,'2025-08-10 19:40:59');
INSERT INTO `parts` VALUES ('R007',241,'Oboe',NULL,NULL,'L',1,'4361e7ab.pdf',1,0,'2025-08-10 19:39:28');
INSERT INTO `parts` VALUES ('R007',242,'Bassoon',NULL,NULL,'L',1,'a597ffb6.pdf',1,0,'2025-08-10 19:39:34');
/*!40000 ALTER TABLE `parts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset`
--

DROP TABLE IF EXISTS `password_reset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset` (
  `password_reset_id` int(11) NOT NULL AUTO_INCREMENT,
  `password_reset_email` text NOT NULL,
  `password_reset_selector` text NOT NULL,
  `password_reset_token` longtext NOT NULL,
  `password_reset_expires` text NOT NULL,
  `username` varchar(128) DEFAULT NULL COMMENT 'Username for email verification requests',
  `name` varchar(255) DEFAULT NULL COMMENT 'Full name for email verification requests',
  `password_hash` varchar(128) DEFAULT NULL COMMENT 'Hashed password for email verification requests',
  `request_type` enum('password_reset','email_verification') DEFAULT 'password_reset' COMMENT 'Type of request: password reset or email verification',
  PRIMARY KEY (`password_reset_id`),
  KEY `idx_request_type` (`request_type`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset`
--

LOCK TABLES `password_reset` WRITE;
/*!40000 ALTER TABLE `password_reset` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playgram_items`
--

DROP TABLE IF EXISTS `playgram_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `playgram_items` (
  `id_playgram_item` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique playgram item ID',
  `id_playgram` int(11) NOT NULL COMMENT 'Reference to the playgram parent',
  `catalog_number` varchar(20) NOT NULL COMMENT 'Which piece from the Compositions is on this playgram',
  `comp_order` int(11) NOT NULL COMMENT 'Order of this piece in the performance',
  PRIMARY KEY (`id_playgram_item`),
  UNIQUE KEY `id_playgram_item` (`id_playgram_item`,`comp_order`),
  KEY `fk_playgram_items_idfk_1` (`id_playgram`),
  KEY `fk_playgram_items_idfk_2` (`catalog_number`),
  CONSTRAINT `fk_playgram_items_idfk_1` FOREIGN KEY (`id_playgram`) REFERENCES `playgrams` (`id_playgram`) ON DELETE CASCADE,
  CONSTRAINT `fk_playgram_items_idfk_2` FOREIGN KEY (`catalog_number`) REFERENCES `compositions` (`catalog_number`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1045 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='This table keeps the list of playgram items.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playgram_items`
--

LOCK TABLES `playgram_items` WRITE;
/*!40000 ALTER TABLE `playgram_items` DISABLE KEYS */;
INSERT INTO `playgram_items` VALUES (1,1,'M001',1);
INSERT INTO `playgram_items` VALUES (2,1,'M002',2);
INSERT INTO `playgram_items` VALUES (3,2,'C001',1);
INSERT INTO `playgram_items` VALUES (4,3,'H001',1);
INSERT INTO `playgram_items` VALUES (5,4,'J001',1);
INSERT INTO `playgram_items` VALUES (6,5,'M001',1);
INSERT INTO `playgram_items` VALUES (7,5,'M002',2);
/*!40000 ALTER TABLE `playgram_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `playgrams`
--

DROP TABLE IF EXISTS `playgrams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `playgrams` (
  `id_playgram` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier, incremented number',
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'Title of the playgram (concert program series); must be unique.',
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Complete description of the program, with concert performance notes.',
  `performance_date` date DEFAULT NULL COMMENT 'Date of the first planned performance of this playgram',
  `enabled` int(11) DEFAULT NULL COMMENT 'Set to 1 if enabled, otherwise 0',
  PRIMARY KEY (`id_playgram`),
  UNIQUE KEY `title` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `playgrams`
--

LOCK TABLES `playgrams` WRITE;
/*!40000 ALTER TABLE `playgrams` DISABLE KEYS */;
INSERT INTO `playgrams` VALUES (1,'Patriotic Concert','A patriotic concert featuring American marches and favorites','2024-07-04',1);
INSERT INTO `playgrams` VALUES (2,'Classical Showcase','An evening of classical masterworks arranged for band','2024-03-15',1);
INSERT INTO `playgrams` VALUES (3,'Holiday Concert','Annual Christmas concert featuring seasonal favorites','2024-12-20',1);
INSERT INTO `playgrams` VALUES (4,'Jazz Night','An evening of jazz and popular music','2024-02-14',1);
INSERT INTO `playgrams` VALUES (5,'March Madness','Concert featuring the best American marches','2024-06-16',1);
/*!40000 ALTER TABLE `playgrams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recordings`
--

DROP TABLE IF EXISTS `recordings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `recordings` (
  `id_recording` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique ID for this recording',
  `catalog_number` varchar(5) NOT NULL COMMENT 'Catalog number of the composition',
  `id_concert` int(11) NOT NULL COMMENT 'Which concert this recording is from',
  `name` varchar(255) DEFAULT NULL COMMENT 'Name of the piece or excerpt on the recording',
  `ensemble` varchar(2048) DEFAULT NULL COMMENT 'Ensemble or performer name',
  `id_ensemble` varchar(4) DEFAULT NULL COMMENT 'Ensemble or performer from the ensembles table',
  `link` varchar(512) DEFAULT NULL COMMENT 'URL or path to the audio or video recording',
  `notes` text NOT NULL COMMENT 'Notes about this recording for the concert',
  `composer` varchar(255) DEFAULT NULL COMMENT 'Composer for labeling purposes',
  `arranger` varchar(255) DEFAULT NULL COMMENT 'Arranger for labeling purposes',
  `enabled` int(11) NOT NULL DEFAULT 0 COMMENT 'Enable flag for display or availability',
  PRIMARY KEY (`id_recording`),
  KEY `catalog_number` (`catalog_number`),
  KEY `id_concert` (`id_concert`),
  KEY `audio_files_ibfk_3` (`id_ensemble`),
  CONSTRAINT `recordings_ibfk_1` FOREIGN KEY (`catalog_number`) REFERENCES `compositions` (`catalog_number`),
  CONSTRAINT `recordings_ibfk_2` FOREIGN KEY (`id_concert`) REFERENCES `concerts` (`id_concert`),
  CONSTRAINT `recordings_ibfk_3` FOREIGN KEY (`id_ensemble`) REFERENCES `ensembles` (`id_ensemble`)
) ENGINE=InnoDB AUTO_INCREMENT=1023 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='This table keeps track of recordings.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recordings`
--

LOCK TABLES `recordings` WRITE;
/*!40000 ALTER TABLE `recordings` DISABLE KEYS */;
INSERT INTO `recordings` VALUES (1,'M001',1,'El Capitan - Live Recording','Community Concert Band','CB','recordings/M001_20240704.mp3','Excellent performance with crisp articulation and good balance','John Philip Sousa','',1);
INSERT INTO `recordings` VALUES (2,'M002',1,'Stars and Stripes Forever - Live','Community Concert Band','CB','recordings/M002_20240704.mp3','Outstanding piccolo solo by Sarah Williams','John Philip Sousa','',1);
INSERT INTO `recordings` VALUES (3,'H001',2,'A Christmas Festival - Live','Community Concert Band','CB','recordings/H001_20231220.mp3','Beautiful holiday concert with full choir accompaniment','Traditional','Leroy Anderson',1);
INSERT INTO `recordings` VALUES (4,'C001',3,'William Tell Overture - Live','Community Concert Band','CB','recordings/C001_20240315.mp3','Challenging piece performed with great skill','Gioachino Rossini','Various',1);
INSERT INTO `recordings` VALUES (5,'J001',4,'In the Mood - Live','Community Concert Band','CB','recordings/J001_20240214.mp3','Swinging performance with great saxophone section','Joe Garland','Various',1);
/*!40000 ALTER TABLE `recordings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `section_part_types`
--

DROP TABLE IF EXISTS `section_part_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `section_part_types` (
  `id_section` int(10) unsigned NOT NULL COMMENT 'Section ID',
  `id_part_type` int(10) unsigned NOT NULL COMMENT 'Part type ID',
  PRIMARY KEY (`id_section`,`id_part_type`),
  KEY `fk_part_type` (`id_part_type`),
  CONSTRAINT `fk_part_type` FOREIGN KEY (`id_part_type`) REFERENCES `part_types` (`id_part_type`) ON DELETE CASCADE,
  CONSTRAINT `fk_section` FOREIGN KEY (`id_section`) REFERENCES `sections` (`id_section`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Links sections to part types (many-to-many)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `section_part_types`
--

LOCK TABLES `section_part_types` WRITE;
/*!40000 ALTER TABLE `section_part_types` DISABLE KEYS */;
INSERT INTO `section_part_types` VALUES (1,1);
INSERT INTO `section_part_types` VALUES (1,2);
INSERT INTO `section_part_types` VALUES (1,3);
INSERT INTO `section_part_types` VALUES (1,4);
INSERT INTO `section_part_types` VALUES (1,5);
INSERT INTO `section_part_types` VALUES (1,6);
INSERT INTO `section_part_types` VALUES (1,7);
INSERT INTO `section_part_types` VALUES (1,8);
INSERT INTO `section_part_types` VALUES (1,9);
INSERT INTO `section_part_types` VALUES (1,10);
INSERT INTO `section_part_types` VALUES (1,11);
INSERT INTO `section_part_types` VALUES (1,12);
INSERT INTO `section_part_types` VALUES (1,13);
INSERT INTO `section_part_types` VALUES (1,14);
INSERT INTO `section_part_types` VALUES (1,15);
INSERT INTO `section_part_types` VALUES (1,16);
INSERT INTO `section_part_types` VALUES (1,17);
INSERT INTO `section_part_types` VALUES (2,18);
INSERT INTO `section_part_types` VALUES (2,19);
INSERT INTO `section_part_types` VALUES (2,20);
INSERT INTO `section_part_types` VALUES (2,21);
INSERT INTO `section_part_types` VALUES (2,22);
INSERT INTO `section_part_types` VALUES (2,23);
INSERT INTO `section_part_types` VALUES (2,24);
INSERT INTO `section_part_types` VALUES (2,25);
INSERT INTO `section_part_types` VALUES (2,26);
INSERT INTO `section_part_types` VALUES (2,27);
INSERT INTO `section_part_types` VALUES (2,28);
INSERT INTO `section_part_types` VALUES (2,29);
INSERT INTO `section_part_types` VALUES (2,30);
INSERT INTO `section_part_types` VALUES (3,31);
INSERT INTO `section_part_types` VALUES (3,32);
INSERT INTO `section_part_types` VALUES (3,33);
INSERT INTO `section_part_types` VALUES (3,34);
INSERT INTO `section_part_types` VALUES (3,35);
/*!40000 ALTER TABLE `section_part_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sections`
--

DROP TABLE IF EXISTS `sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sections` (
  `id_section` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique ID for the section',
  `name` varchar(255) NOT NULL COMMENT 'Section name, e.g. Brass, Woodwinds, Percussion',
  `description` varchar(1024) DEFAULT NULL COMMENT 'Description of the section',
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT 1 COMMENT 'Set to 1 to enable this section',
  `section_leader` int(10) unsigned DEFAULT NULL COMMENT 'User ID of the section leader',
  PRIMARY KEY (`id_section`),
  KEY `fk_section_leader` (`section_leader`),
  CONSTRAINT `fk_section_leader` FOREIGN KEY (`section_leader`) REFERENCES `users` (`id_users`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='Groups of part types (sections, e.g. Brass, Woodwinds)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sections`
--

LOCK TABLES `sections` WRITE;
/*!40000 ALTER TABLE `sections` DISABLE KEYS */;
INSERT INTO `sections` VALUES (1,'Woodwinds','Flutes, oboes, clarinets, saxophones, bassoons',1,NULL);
INSERT INTO `sections` VALUES (2,'Brass','Trumpets, horns, trombones, euphoniums, tubas',1,NULL);
INSERT INTO `sections` VALUES (3,'Percussion','Timpani, drums, mallet instruments, accessories',1,NULL);
INSERT INTO `sections` VALUES (4,'Strings','Violins, violas, cellos, double basses',1,NULL);
INSERT INTO `sections` VALUES (5,'Other','Special instruments and soloists',1,NULL);
/*!40000 ALTER TABLE `sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id_users` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique ID for the user',
  `username` varchar(128) NOT NULL COMMENT 'The user name',
  `password` varchar(128) NOT NULL COMMENT 'User password',
  `name` varchar(255) DEFAULT NULL COMMENT 'Real name',
  `address` varchar(255) DEFAULT NULL COMMENT 'Users e-mail address',
  `roles` varchar(255) DEFAULT NULL COMMENT 'Text field containing roles',
  PRIMARY KEY (`id_users`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci COMMENT='This table keeps users.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$1K2PKUCxjWpxsSuLqsb/o.Qx3pRf2eThDAjf.C7jiVXTFXa/xg/Q6','System Administrator','admin@musiclibrary.org','administrator');
INSERT INTO `users` VALUES (2,'librarian','$2y$10$s.ZoclJFRAKIsHZuSX3GG.Lr3aSNwjK39AXb6naaYNzmcfysmfXq6','Music Librarian','librarian@musiclibrary.org','librarian');
INSERT INTO `users` VALUES (3,'conductor','$2y$10$FdEob9VsvTjnTsxv4ySnEOvn/14OOrEjnVE2QHqW.k729vsTZFcpq','John Smith','conductor@musiclibrary.org','user');
INSERT INTO `users` VALUES (4,'user','$2y$10$uEjJG/pxxt6kPu5ad32D6uVyNtBzFJIqSaVrtjbtAYAJPQ.ABiujq','General User','user@musiclibrary.org','user');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-08-17 13:17:41
