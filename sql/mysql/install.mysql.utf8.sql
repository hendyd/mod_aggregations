CREATE TABLE IF NOT EXISTS `#__aggregation_form` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`userid` int(11) DEFAULT NULL,
	`category` varchar(255) DEFAULT NULL,
	`subcategory` varchar(255) DEFAULT NULL,
	`data` varchar(25000) DEFAULT NULL,
	`date_submitted` DATETIME DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__crm_categories` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(255) DEFAULT NULL,
	`crm_value` varchar(255) DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

INSERT INTO `#__crm_categories` (`name`, `crm_value`) VALUES
('Business Services', 'business'),
('Facilities Management', 'facilities'),
('ICT', 'ict'),
('Utilities', 'utilities'),
('Other', 'other');

CREATE TABLE IF NOT EXISTS `#__crm_subcategories` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(255) NOT NULL,
	`cat_id` int(11) NOT NULL,
	`crm_value` varchar(255) NOT NULL,
	`mod_aggregation_supplier_form_field` varchar(255) NOT NULL
PRIMARY KEY (`id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

INSERT INTO `#__crm_subcategories` (`name`, `cat_id`, `crm_value`, `mod_aggregation_supplier_form_field`) VALUES
('Auditor Services', 1, 'Auditor Services', 'select-supplier-auditor'),
('Building and Facilities Management Services', 2, 'Building and Facilities Management Services', 'select-supplier-bfms'),
('Catering', 2, 'Catering', 'select-supplier-catering'),
('Cleaning', 2, 'Cleaning', 'select-supplier-cleaning'),
('Consultancy', 1, 'Consultancy', 'select-supplier-consultancy'),
('DBS Checks', 1, 'DBS_Checks', 'select-supplier-dbs'),
('Educational Supplies', 1, 'Educational', 'select-supplier-educational'),
('Electricity', 4, 'Electricity', 'select-supplier-electricity'),
('Energy Management Services', 1, 'Energy Management Services', 'select-supplier-ems'),
('Financial Software', 3, 'Financial Software', 'select-supplier-finsoft'),
('Fire Safety', 2, 'Fire Safety', 'select-supplier-fire'),
('Furniture', 2, 'Furniture', 'select-supplier-furniture'),
('Gas', 4, 'Gas', 'select-supplier-gas'),
('Grounds Maintenance', 2, 'Grounds Maintenance', 'select-supplier-groundsmain'),
('Human Resources', 1, 'Human Resources', 'select-supplier-hr'),
('Insurance', 1, 'Insurance', 'select-supplier-insurance'),
('IT Hardware', 3, 'IT Hardware', 'select-supplier-ithard'),
('IT Maintenance and Support', 3, 'IT Maintenance and Support', 'select-supplier-itmain'),
('IT Software', 3, 'IT Software', 'select-supplier-itsoft'),
('LED Lighting', 2, 'LED Lighting', 'select-supplier-led'),
('Legal', 1, 'Legal', 'select-supplier-legal'),
('Mechanical and Electrical Engineering Services', 2, 'Mechanical and Electrical Engineering Services', 'select-supplier-mees'),
('Oil', 4, 'Oil', 'select-supplier-oil'),
('Payroll', 1, 'Payroll', 'select-supplier-payroll'),
('Photocopying', 3, 'Photocopying', 'select-supplier-photo'),
('Project Management Services', 1, 'Project Management Services', 'select-supplier-pms'),
('Security Services', 2, 'Security Services', 'select-supplier-security'),
('Solar Energy Panels', 2, 'Solar Energy Panels', 'select-supplier-solar'),
('Stationery', 1, 'Stationery', 'select-supplier-stationery'),
('Supply and Agency Staff', 1, 'Supply and Agency Staff', 'select-supplier-supplyagency'),
('Telecoms Broadband', 3, 'Telecoms Broadband', 'select-supplier-broadband'),
('Telecoms Landline', 3, 'Telecoms Landline', 'select-supplier-landline'),
('Telecoms Mobile', 3, 'Telecoms Mobile', 'select-supplier-mobile'),
('Telecoms Systems', 3, 'Telecoms Systems', 'select-supplier-telesys'),
('Transport Services', 1, 'Transport Services', 'select-supplier-transport'),
('Waste Management', 2, 'Waste Management', 'select-supplier-waste'),
('Water', 4, 'Water', 'select-supplier-water'),
('Web Development', 3, 'Web Development', 'select-supplier-webdev');

ALTER TABLE `#__crm_subcategories` 
ADD INDEX `crm_cat_subcat_idx` (`cat_id` ASC);
;
ALTER TABLE `#__crm_subcategories` 
ADD CONSTRAINT `crm_cat_subcat`
  FOREIGN KEY (`cat_id`)
  REFERENCES `#__crm_categories` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;