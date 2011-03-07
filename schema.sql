-- Table structure for table `dirs`
--

CREATE TABLE IF NOT EXISTS `dirs` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `dirname` varchar(255) NOT NULL,
	  `bucket` varchar(255) NOT NULL,
	  `parent_id` int(11) NOT NULL,
	  `created` datetime NOT NULL,
	  `updated` datetime NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `dirname` (`dirname`),
	  KEY `bucket` (`bucket`)
	) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

	-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE IF NOT EXISTS `images` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `name` varchar(500) NOT NULL,
	  `time` int(11) NOT NULL,
	  `size` int(11) NOT NULL,
	  `hash` varchar(25) NOT NULL,
	  `dir_id` int(11) NOT NULL,
	  `created` datetime NOT NULL,
	  `updated` datetime NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `name` (`name`),
	  KEY `parent_id` (`dir_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

