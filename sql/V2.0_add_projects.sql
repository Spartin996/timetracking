CREATE TABLE `projects` (
  `id` int NOT NULL,
  `project_cat` int DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_closed` datetime DEFAULT NULL,
  `project_desc` longtext DEFAULT NULL,
  `minutes` int NOT NULL,
  `steps` int DEFAULT NULL,
  `steps_complete` int DEFAULT NULL,
  `steps_incomplete` int DEFAULT NULL
);

ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `projects`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;
