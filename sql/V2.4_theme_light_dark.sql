-- Expand theme colours into light/dark palettes with shared defaults.
-- Existing colour settings remain the light-mode values.

UPDATE `settings` SET `description` = 'Light mode: primary background (nav, buttons)' WHERE `setting` = 'primary_background';
UPDATE `settings` SET `description` = 'Light mode: secondary background (hover, accents)' WHERE `setting` = 'secondary_background';
UPDATE `settings` SET `description` = 'Light mode: active / highlight background' WHERE `setting` = 'active_background';
UPDATE `settings` SET `description` = 'Light mode: surface colour (cards, panels)' WHERE `setting` = 'neutral_white';
UPDATE `settings` SET `description` = 'Light mode: muted grey (borders, shadows)' WHERE `setting` = 'neutral_gray';
UPDATE `settings` SET `description` = 'Light mode: neutral active highlight' WHERE `setting` = 'neutral_active';

INSERT INTO `settings` (`setting`, `value`, `description`) VALUES
('theme_mode', 'system', 'Colour theme mode: light, dark, or system (follow OS preference).'),
('page_background', '#faf8f5', 'Light mode: page background'),
('text_color', '#2c2416', 'Light mode: main text colour'),
('border_color', '#d0c4b4', 'Light mode: border colour'),
('on_primary', '#ffffff', 'Light mode: text/icons on primary backgrounds'),
('dark_primary_background', '#a65f28', 'Dark mode: primary background (nav, buttons)'),
('dark_secondary_background', '#c9842e', 'Dark mode: secondary background (hover, accents)'),
('dark_active_background', '#c9a227', 'Dark mode: active / highlight background'),
('dark_neutral_white', '#2a2420', 'Dark mode: surface colour (cards, panels)'),
('dark_neutral_gray', '#8a8078', 'Dark mode: muted grey (borders, shadows)'),
('dark_neutral_active', '#5a4a28', 'Dark mode: neutral active highlight'),
('dark_page_background', '#14110e', 'Dark mode: page background'),
('dark_text_color', '#ede6dc', 'Dark mode: main text colour'),
('dark_border_color', '#3d342c', 'Dark mode: border colour'),
('dark_on_primary', '#fff8f0', 'Dark mode: text/icons on primary backgrounds');
