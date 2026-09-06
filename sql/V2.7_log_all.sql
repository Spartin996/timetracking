-- Verbose SQL/action logging. Default off; enable from Settings when diagnosing.

INSERT INTO `settings` (`setting`, `value`, `description`) VALUES
('log_all', 'N', 'Log all SQL and actions to logfile.log. Leave off unless diagnosing a problem.');
