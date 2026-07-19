-- Seed a starter set of categories when the table is empty.
INSERT INTO `categories` (`display_name`, `description`, `active`, `entries`, `projects`, `seq`)
SELECT d.`display_name`, d.`description`, d.`active`, d.`entries`, d.`projects`, d.`seq`
FROM (
  SELECT 'Admin' AS `display_name`, 'Email, paperwork and admin tasks' AS `description`, 'Y' AS `active`, 'Y' AS `entries`, 'Y' AS `projects`, 1 AS `seq`
  UNION ALL SELECT 'Meetings', 'Meetings and calls', 'Y', 'Y', 'Y', 2
  UNION ALL SELECT 'Development', 'Coding and technical work', 'Y', 'Y', 'Y', 3
  UNION ALL SELECT 'Support', 'Helping others or support work', 'Y', 'Y', 'Y', 4
  UNION ALL SELECT 'Planning', 'Planning and research', 'Y', 'Y', 'Y', 5
  UNION ALL SELECT 'Training', 'Learning and training', 'Y', 'Y', 'Y', 6
  UNION ALL SELECT 'Break', 'Breaks and downtime', 'Y', 'Y', 'N', 7
) AS d
WHERE NOT EXISTS (SELECT 1 FROM `categories` LIMIT 1);
