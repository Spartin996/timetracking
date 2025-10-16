START TRANSACTION;

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `setting` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL
) ;



INSERT INTO `settings` (`id`, `setting`, `value`, `description`) VALUES
(1, 'name', 'Track Times', 'System name used in tab title and menu headings.'),
(2, 'primary_background', 'RED', 'The primary background colour'),
(3, 'secondary_background', 'blue', ''),
(4, 'neutral_white', 'white', ''),
(5, 'neutral_gray', 'Gray', ''),
(6, 'neutral_active', 'yellow', ''),
(7, 'header_img', '../images/Bar.jpg', ''),
(8, 'date_view', 'day_pretty', 'Preferred date view for reports.'),
(9, 'active_background', 'BLUE', 'The primary background colour when active');

ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;