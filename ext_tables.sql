#
# Table structure for table 'tx_glossary2_domain_model_glossary'
#
CREATE TABLE tx_glossary2_domain_model_glossary
(
	title        varchar(255)  DEFAULT '' NOT NULL,
	path_segment varchar(2048) DEFAULT '' NOT NULL,
	description  text,
	images       varchar(255)  DEFAULT '' NOT NULL
);
