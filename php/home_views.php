<?php
/**
 * Home page view helpers: Compact, Standard (via index), Calendar + follow-ups.
 */

/**
 * Normalize home_view setting.
 */
function normalizeHomeView($view)
{
  $view = strtolower((string) $view);
  if (!in_array($view, ['compact', 'standard', 'calendar'], true)) {
    return 'standard';
  }
  return $view;
}

/**
 * Normalize calendar_span setting.
 */
function normalizeCalendarSpan($span)
{
  $span = strtolower((string) $span);
  if (!in_array($span, ['day', 'week'], true)) {
    return 'week';
  }
  return $span;
}

/**
 * Stable pastel-ish colour from a category id (no colour column in DB).
 */
function categoryColor($categoryId)
{
  $hue = ((int) $categoryId * 47) % 360;
  return "hsl({$hue}, 42%, 42%)";
}

/**
 * View switcher control for the home page.
 */
function homeViewSwitcher($currentView)
{
  $currentView = normalizeHomeView($currentView);
  $views = [
    'compact' => 'Compact',
    'standard' => 'Standard',
    'calendar' => 'Calendar',
  ];

  $html = "<div class='home-view-switcher' role='toolbar' aria-label='Home view'>";
  foreach ($views as $key => $label) {
    $active = ($key === $currentView) ? ' is-active' : '';
    $html .= "<button type='button' class='home-view-btn{$active}' data-home-view='{$key}' onclick='switchHomeView(\"{$key}\")'>{$label}</button>";
  }
  $html .= "</div>";
  return $html;
}

/**
 * Shared comment / tags / interrupted fields for tracker panels.
 */
function trackerEntryFields($comment = '', $tags = '')
{
  $tagsEsc = htmlspecialchars((string) $tags, ENT_QUOTES, 'UTF-8');
  return "<div class='compact-fields'>"
    . "<div class='compact-field compact-comment'>"
    . "<label for='comment-editor'>Comment</label>"
    . quillEditorMarkup('comment', $comment)
    . "</div>"
    . "<div class='compact-field compact-interrupted'>"
    . "<label for='interrupted'><input name='interrupted' id='interrupted' type='checkbox' value='Y'> Interrupted</label>"
    . "</div>"
    . "<div class='compact-field tags'>"
    . "<div><label for='addTags'>Add Tags:</label> "
    . "<input type='text' name='addTags' id='addTags' onkeyup='showTags(this.value)'></div>"
    . "<div id='divTags'></div>"
    . "<input type='hidden' name='tags' id='tags' value='{$tagsEsc}'>"
    . "<div id='displayTags'></div>"
    . "</div>"
    . "</div>";
}

/**
 * Compact tracker panel (active job or quick start).
 */
function compactTrackerMarkup()
{
  global $conn;

  $sql = "SELECT entries.id, categories_id, display_name, start_time, end_time, comment, tags
    FROM entries
    LEFT JOIN categories ON entries.categories_id = categories.id
    WHERE end_time IS NULL
    LIMIT 1";
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  $row = mysqli_fetch_array($result);

  $html = "<div class='compact-tracker' id='compactTracker'>";

  if ($row) {
    $catId = (int) $row['categories_id'];
    $name = htmlspecialchars($row['display_name'], ENT_QUOTES, 'UTF-8');
    $startUnix = (int) strtotime($row['start_time']);
    $html .= "<div class='compact-active'>";
    $html .= "<input type='hidden' id='compactCurCat' value='{$catId}'>";
    $html .= "<input type='hidden' id='timerValue' value='{$startUnix}'>";
    $html .= "<div class='compact-active-meta'>";
    $html .= "<span class='compact-cat-name'>{$name}</span>";
    $html .= "<span class='compact-timer' id='timer'></span>";
    $html .= "</div>";
    $html .= trackerEntryFields($row['comment'], $row['tags']);
    $html .= "<div class='compact-actions'>";
    $html .= "<label class='compact-switch-label'>Switch to " . CategoryDropList('entries', 'N') . "</label>";
    $html .= "<button type='button' class='compact-btn compact-btn-switch' onclick='compactSwitchJob()'>Switch</button>";
    $html .= "<button type='button' class='compact-btn compact-btn-stop' onclick='compactStopJob()'>Stop</button>";
    $html .= "</div>";
    $html .= "</div>";
  } else {
    $html .= "<div class='compact-idle'>";
    $html .= "<input type='hidden' id='timerValue' value='No Open Job'>";
    $html .= "<div class='compact-active-meta'>";
    $html .= "<span class='compact-cat-name'>No open job</span>";
    $html .= "<span class='compact-timer' id='timer'></span>";
    $html .= "</div>";
    $html .= "<div class='compact-actions'>";
    $html .= CategoryDropList('entries', 'N');
    $html .= "<button type='button' class='compact-btn compact-btn-start' onclick='compactStartJob()'>Start</button>";
    $html .= "</div>";
    $html .= "</div>";
  }

  $html .= "</div>";
  return $html;
}

/**
 * Today's entries list for compact home.
 */
function compactRecentEntries()
{
  global $conn;

  $dayStart = date('Y-m-d') . ' 00:00:00';
  $dayEnd = date('Y-m-d') . ' 23:59:59';
  $dayStart = $conn->real_escape_string(displayTime($dayStart, 'sql'));
  $dayEnd = $conn->real_escape_string(displayTime($dayEnd, 'sql'));

  $sql = "SELECT entries.id, display_name, start_time, end_time, entries.minutes, interrupted
    FROM entries
    LEFT JOIN categories ON entries.categories_id = categories.id
    WHERE start_time >= '{$dayStart}'
      AND start_time <= '{$dayEnd}'
    ORDER BY start_time DESC";
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");

  $html = "<div class='compact-recent' id='compactRecent'>";
  $html .= "<h3>Today</h3>";
  $html .= "<ul class='compact-recent-list'>";

  $count = 0;
  while ($row = mysqli_fetch_array($result)) {
    $count++;
    $id = (int) $row['id'];
    $name = htmlspecialchars($row['display_name'], ENT_QUOTES, 'UTF-8');
    $start = htmlspecialchars(displayTime($row['start_time'], setting('date_view')), ENT_QUOTES, 'UTF-8');
    $end = $row['end_time']
      ? htmlspecialchars(displayTime($row['end_time'], setting('date_view')), ENT_QUOTES, 'UTF-8')
      : '<span id="ongoing">ONGOING</span>';
    $mins = $row['end_time'] ? minutesToHours($row['minutes']) : '—';
    $interrupted = ($row['interrupted'] === 'Y') ? " <span class='badge-interrupted'>I</span>" : '';
    $html .= "<li class='compact-recent-item' onclick='newWindow(`entries.php?id={$id}`)'>";
    $html .= "<span class='compact-recent-cat'>{$name}{$interrupted}</span>";
    $html .= "<span class='compact-recent-time'>{$start} → {$end}</span>";
    $html .= "<span class='compact-recent-mins'>{$mins}</span>";
    $html .= "</li>";
  }

  if ($count === 0) {
    $html .= "<li class='compact-recent-empty'>No entries today.</li>";
  }

  $html .= "</ul></div>";
  return $html;
}

/**
 * Full compact home layout.
 */
function renderCompactHome()
{
  return "<div class='home-compact' id='homeCompact'>"
    . compactTrackerMarkup()
    . compactRecentEntries()
    . "</div>";
}

/**
 * Entries for a datetime range (lean rows for calendar).
 *
 * @return array<int, array>
 */
function getEntriesForRange($start, $end)
{
  global $conn;
  $start = $conn->real_escape_string($start);
  $end = $conn->real_escape_string($end);

  $sql = "SELECT entries.id, categories_id, display_name, start_time, end_time, entries.minutes,
      interrupted, follow_up, comment, tags, project_id, projects.title AS project_title
    FROM entries
    LEFT JOIN categories ON entries.categories_id = categories.id
    LEFT JOIN projects ON entries.project_id = projects.id
    WHERE start_time >= '{$start}'
      AND start_time <= '{$end}'
    ORDER BY start_time ASC";
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");

  $rows = [];
  while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
  }
  return $rows;
}

/**
 * Open projects with incomplete checklist steps.
 *
 * @return array<int, array>
 */
function getFollowUpProjects()
{
  global $conn;
  $sql = "SELECT id, title, steps_incomplete, steps_complete, steps
    FROM projects
    WHERE date_closed IS NULL
      AND steps_incomplete > 0
    ORDER BY steps_incomplete DESC, title ASC";
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");

  $rows = [];
  while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
  }
  return $rows;
}

/**
 * Group entries by Y-m-d date key.
 *
 * @param array<int, array> $entries
 * @return array<string, array>
 */
function groupEntriesByDay(array $entries)
{
  $byDay = [];
  foreach ($entries as $entry) {
    $day = substr($entry['start_time'], 0, 10);
    if (!isset($byDay[$day])) {
      $byDay[$day] = [];
    }
    $byDay[$day][] = $entry;
  }
  return $byDay;
}

/**
 * Untracked gaps ≥ threshold minutes between consecutive entries per day.
 *
 * @param array<string, array> $entriesByDay
 * @return array<int, array{day:string,start:string,end:string,minutes:int}>
 */
function getTimeGaps(array $entriesByDay, $thresholdMinutes = 15)
{
  $thresholdMinutes = (int) $thresholdMinutes;
  $gaps = [];

  foreach ($entriesByDay as $day => $dayEntries) {
    usort($dayEntries, function ($a, $b) {
      return strcmp($a['start_time'], $b['start_time']);
    });

    $count = count($dayEntries);
    for ($i = 0; $i < $count - 1; $i++) {
      $end = $dayEntries[$i]['end_time'];
      $nextStart = $dayEntries[$i + 1]['start_time'];
      if ($end === null || $end === '') {
        continue;
      }
      $gapMinutes = (int) floor((strtotime($nextStart) - strtotime($end)) / 60);
      if ($gapMinutes >= $thresholdMinutes) {
        $gaps[] = [
          'day' => $day,
          'start' => $end,
          'end' => $nextStart,
          'minutes' => $gapMinutes,
        ];
      }
    }
  }

  return $gaps;
}

/**
 * Monday (Y-m-d) for the week containing $date.
 */
function weekStartMonday($date)
{
  $ts = strtotime($date);
  $dow = (int) date('N', $ts); // 1=Mon … 7=Sun
  return date('Y-m-d', strtotime('-' . ($dow - 1) . ' days', $ts));
}

/**
 * Default calendar window: 7:30 AM – 5:30 PM (minutes from midnight).
 *
 * @return array{startMin:int,endMin:int}
 */
function calendarDefaultBounds()
{
  return [
    'startMin' => 7 * 60 + 30,
    'endMin' => 17 * 60 + 30,
  ];
}

/**
 * Slot size in minutes: day view uses 15-minute increments, week uses hours.
 */
function calendarSlotMinutes($span = 'week')
{
  return normalizeCalendarSpan($span) === 'day' ? 15 : 60;
}

/**
 * Minutes-from-midnight for a datetime string.
 */
function calendarMinuteOfDay($dateTime)
{
  $ts = strtotime($dateTime);
  return ((int) date('H', $ts)) * 60 + (int) date('i', $ts);
}

/**
 * Entry end minute-of-day (open jobs use now / fallback duration).
 */
function calendarEntryEndMinute(array $entry, $slotMinutes = 15)
{
  $startMin = calendarMinuteOfDay($entry['start_time']);
  if (!empty($entry['end_time'])) {
    $endMin = calendarMinuteOfDay($entry['end_time']);
    // Overnight: treat as extending to end of day for bounds purposes.
    if ($endMin <= $startMin) {
      return 24 * 60;
    }
    return $endMin;
  }
  $nowMin = ((int) date('H')) * 60 + (int) date('i');
  $endMin = max($startMin + $slotMinutes, $nowMin);
  if (!empty($entry['minutes'])) {
    $endMin = max($endMin, $startMin + (int) $entry['minutes']);
  }
  return min($endMin, 24 * 60);
}

/**
 * Visible calendar bounds for a set of entries.
 * Default 7:30–17:30; expand to cover out-of-range entries ± 15 minutes,
 * snapped to the slot grid.
 *
 * @param array<int, array> $entries
 * @return array{startMin:int,endMin:int}
 */
function calendarBoundsForEntries(array $entries, $span = 'week')
{
  $defaults = calendarDefaultBounds();
  $startMin = $defaults['startMin'];
  $endMin = $defaults['endMin'];
  $slotMinutes = calendarSlotMinutes($span);
  $pad = 15;

  foreach ($entries as $entry) {
    $entryStart = calendarMinuteOfDay($entry['start_time']);
    $entryEnd = calendarEntryEndMinute($entry, $slotMinutes);

    if ($entryStart < $defaults['startMin']) {
      $startMin = min($startMin, $entryStart - $pad);
    }
    if ($entryEnd > $defaults['endMin']) {
      $endMin = max($endMin, $entryEnd + $pad);
    }
  }

  // Snap outward to slot boundaries.
  $startMin = (int) (floor($startMin / $slotMinutes) * $slotMinutes);
  $endMin = (int) (ceil($endMin / $slotMinutes) * $slotMinutes);

  $startMin = max(0, $startMin);
  $endMin = min(24 * 60, $endMin);
  if ($endMin <= $startMin) {
    $endMin = $startMin + $slotMinutes;
  }

  return [
    'startMin' => $startMin,
    'endMin' => $endMin,
  ];
}

/**
 * Position an entry block within the calendar day column.
 *
 * @param array{startMin:int,endMin:int}|null $bounds
 * @return array{top:float,height:float,clipped:bool}
 */
function calendarBlockGeometry($startTime, $endTime, $minutesFallback = null, $span = 'week', $bounds = null)
{
  if ($bounds === null) {
    $bounds = calendarDefaultBounds();
  }
  $dayStartMin = (int) $bounds['startMin'];
  $dayEndMin = (int) $bounds['endMin'];
  $spanMin = max(1, $dayEndMin - $dayStartMin);
  $slotMinutes = calendarSlotMinutes($span);
  $minBlockMin = 8;

  $startMinOfDay = calendarMinuteOfDay($startTime);

  if ($endTime) {
    $endMinOfDay = calendarMinuteOfDay($endTime);
    if ($endMinOfDay <= $startMinOfDay) {
      $endMinOfDay = $dayEndMin;
    }
  } else {
    $nowMin = ((int) date('H')) * 60 + (int) date('i');
    $endMinOfDay = max($startMinOfDay + $slotMinutes, $nowMin);
    if ($minutesFallback) {
      $endMinOfDay = max($endMinOfDay, $startMinOfDay + (int) $minutesFallback);
    }
  }

  $topMin = max($dayStartMin, min($startMinOfDay, $dayEndMin));
  $bottomMin = max($topMin + $minBlockMin, min($endMinOfDay, $dayEndMin));

  $top = (($topMin - $dayStartMin) / $spanMin) * 100;
  $height = (($bottomMin - $topMin) / $spanMin) * 100;
  $minHeightPct = ($slotMinutes / $spanMin) * 100;

  return [
    'top' => round($top, 3),
    'height' => round(max($height, $minHeightPct * 0.85), 3),
    'clipped' => ($startMinOfDay < $dayStartMin || $endMinOfDay > $dayEndMin),
  ];
}

/**
 * Strip HTML for short comment preview.
 */
function commentSnippet($html, $maxLen = 80)
{
  $text = trim(html_entity_decode(strip_tags((string) $html), ENT_QUOTES, 'UTF-8'));
  $len = function_exists('mb_strlen') ? mb_strlen($text) : strlen($text);
  if ($len > $maxLen) {
    $cut = function_exists('mb_substr')
      ? mb_substr($text, 0, $maxLen - 1)
      : substr($text, 0, $maxLen - 1);
    return htmlspecialchars($cut . '…', ENT_QUOTES, 'UTF-8');
  }
  return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Render a single calendar entry block.
 *
 * @param array{startMin:int,endMin:int}|null $bounds
 */
function calendarEntryBlock(array $entry, $rich = false, $span = 'week', $bounds = null)
{
  $id = (int) $entry['id'];
  $geo = calendarBlockGeometry($entry['start_time'], $entry['end_time'], $entry['minutes'], $span, $bounds);
  $color = categoryColor($entry['categories_id']);
  $name = htmlspecialchars($entry['display_name'], ENT_QUOTES, 'UTF-8');
  $interrupted = ($entry['interrupted'] === 'Y') ? ' is-interrupted' : '';
  $open = empty($entry['end_time']) ? ' is-open' : '';
  $mins = empty($entry['end_time'])
    ? 'ongoing'
    : minutesToHours($entry['minutes']);

  $detail = '';
  if ($rich) {
    $snippet = commentSnippet($entry['comment'] ?? '');
    $project = htmlspecialchars((string) ($entry['project_title'] ?? ''), ENT_QUOTES, 'UTF-8');
    $flag = ($entry['interrupted'] === 'Y') ? '<span class="cal-flag">Interrupted</span>' : '';
    $startLabel = date('H:i', strtotime($entry['start_time']));
    $endLabel = empty($entry['end_time']) ? '…' : date('H:i', strtotime($entry['end_time']));
    $detail = "<span class='cal-block-meta'>{$startLabel}–{$endLabel} · {$mins}"
      . ($project !== '' ? " · {$project}" : '')
      . "</span>";
    if ($snippet !== '') {
      $detail .= "<span class='cal-block-comment'>{$snippet}</span>";
    }
    $detail .= $flag;
  }

  return "<button type='button' class='cal-block{$interrupted}{$open}' data-entry-id='{$id}'"
    . " style='top:{$geo['top']}%;height:{$geo['height']}%;background:{$color}'"
    . " onclick='newWindow(`entries.php?id={$id}`)' title='{$name}'>"
    . "<span class='cal-block-title'>{$name}</span>"
    . $detail
    . "</button>";
}

/**
 * Time gutter labels for calendar.
 *
 * @param array{startMin:int,endMin:int} $bounds
 */
function calendarGutterHtml($span, array $bounds)
{
  $slotMinutes = calendarSlotMinutes($span);
  $html = "<div class='cal-gutter'>";
  for ($minuteOfDay = (int) $bounds['startMin']; $minuteOfDay < (int) $bounds['endMin']; $minuteOfDay += $slotMinutes) {
    $h = intdiv($minuteOfDay, 60);
    $m = $minuteOfDay % 60;
    $isHour = ($m === 0);
    $label = sprintf('%02d:%02d', $h, $m);
    $cls = $isHour ? 'cal-gutter-hour is-hour' : 'cal-gutter-hour is-sub';
    $html .= "<div class='{$cls}'><span>{$label}</span></div>";
  }
  $html .= "</div>";
  return $html;
}

/**
 * Follow-ups panel for calendar view.
 */
function renderFollowUpsPanel(array $entries, $rangeStart, $rangeEnd)
{
  $projects = getFollowUpProjects();
  $byDay = groupEntriesByDay($entries);
  $gaps = getTimeGaps($byDay, 15);

  $followUps = array_values(array_filter($entries, function ($e) {
    return isset($e['follow_up']) && $e['follow_up'] === 'Y';
  }));

  $openJob = null;
  foreach ($entries as $e) {
    if (empty($e['end_time'])) {
      $openJob = $e;
      break;
    }
  }
  // Open job may have started before the range; still surface it.
  if ($openJob === null) {
    global $conn;
    $sql = "SELECT entries.id, display_name, start_time, end_time, interrupted, categories_id
      FROM entries
      LEFT JOIN categories ON entries.categories_id = categories.id
      WHERE end_time IS NULL
      LIMIT 1";
    $result = $conn->query($sql);
    if ($result && ($row = mysqli_fetch_assoc($result))) {
      $openJob = $row;
    }
  }

  $html = "<aside class='cal-followups' id='calFollowups'>";
  $html .= "<h3>Follow-ups</h3>";

  $html .= "<section class='followup-section'>";
  $html .= "<h4>Needs attention</h4>";
  $html .= "<ul class='followup-list'>";

  $attentionCount = 0;
  if ($openJob) {
    $attentionCount++;
    $oid = (int) $openJob['id'];
    $oname = htmlspecialchars($openJob['display_name'], ENT_QUOTES, 'UTF-8');
    $html .= "<li class='followup-open'>"
      . "<button type='button' onclick=\"focusCalendarEntry({$oid})\">Open job: {$oname}</button>"
      . "</li>";
  }

  foreach ($followUps as $e) {
    $attentionCount++;
    $eid = (int) $e['id'];
    $ename = htmlspecialchars($e['display_name'], ENT_QUOTES, 'UTF-8');
    $when = htmlspecialchars(displayTime($e['start_time'], setting('date_view')), ENT_QUOTES, 'UTF-8');
    $html .= "<li class='followup-interrupted'>"
      . "<button type='button' onclick=\"focusCalendarEntry({$eid})\">Follow-up: {$ename}</button>"
      . "<span class='followup-meta'>{$when}</span></li>";
  }

  foreach ($gaps as $g) {
    $attentionCount++;
    $dayLabel = htmlspecialchars($g['day'], ENT_QUOTES, 'UTF-8');
    $dur = minutesToHours($g['minutes']);
    $startH = date('H:i', strtotime($g['start']));
    $endH = date('H:i', strtotime($g['end']));
    $html .= "<li class='followup-gap'>"
      . "<span class='followup-gap-label'>Untracked gap</span>"
      . "<span class='followup-meta'>{$dayLabel} {$startH}–{$endH} ({$dur})</span>"
      . "</li>";
  }

  if ($attentionCount === 0) {
    $html .= "<li class='followup-empty'>Nothing flagged for this range.</li>";
  }

  $html .= "</ul></section>";

  $html .= "<section class='followup-section'>";
  $html .= "<h4>Open projects</h4>";
  if ($projects === []) {
    $html .= "<p class='followup-empty'>No incomplete project steps.</p>";
  } else {
    $html .= "<ul class='followup-list'>";
    foreach ($projects as $p) {
      $pid = (int) $p['id'];
      $title = htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8');
      $left = (int) $p['steps_incomplete'];
      $html .= "<li><a href='project_edit.php?id={$pid}'>{$title}</a>"
        . "<span class='followup-meta'>{$left} step" . ($left === 1 ? '' : 's') . " left</span></li>";
    }
    $html .= "</ul>";
  }
  $html .= "</section>";

  $html .= "</aside>";
  return $html;
}

/**
 * Day or week calendar grid.
 */
function renderCalendarGrid($anchorDate, $span, array $entries)
{
  $span = normalizeCalendarSpan($span);
  $bounds = calendarBoundsForEntries($entries, $span);
  $byDay = groupEntriesByDay($entries);

  if ($span === 'day') {
    $days = [$anchorDate];
  } else {
    $monday = weekStartMonday($anchorDate);
    $days = [];
    for ($i = 0; $i < 7; $i++) {
      $days[] = date('Y-m-d', strtotime("+{$i} days", strtotime($monday)));
    }
  }

  $rich = ($span === 'day');
  $colsClass = ($span === 'day') ? 'cal-grid-day' : 'cal-grid-week';
  $slotMinutes = calendarSlotMinutes($span);
  $totalMin = (int) $bounds['endMin'] - (int) $bounds['startMin'];
  $slotCount = max(1, (int) ($totalMin / $slotMinutes));
  $bodyMinHeight = ($span === 'day') ? ($slotCount * 28) : max(420, $slotCount * 28);

  $html = "<div class='cal-grid {$colsClass}' data-slot-minutes='{$slotMinutes}'"
    . " data-start-min='{$bounds['startMin']}' data-end-min='{$bounds['endMin']}'>";
  $html .= calendarGutterHtml($span, $bounds);
  $html .= "<div class='cal-days'>";

  foreach ($days as $day) {
    $label = date('D j M', strtotime($day));
    $isToday = ($day === date('Y-m-d')) ? ' is-today' : '';
    $dayEntries = $byDay[$day] ?? [];
    $html .= "<div class='cal-day{$isToday}' data-day='{$day}'>";
    $html .= "<div class='cal-day-head'>{$label}</div>";
    $html .= "<div class='cal-day-body' style='min-height:{$bodyMinHeight}px'>";
    for ($i = 0; $i < $slotCount; $i++) {
      $minuteOfDay = (int) $bounds['startMin'] + ($i * $slotMinutes);
      $slotClass = 'cal-hour-slot';
      if (($minuteOfDay % 60) === 0) {
        $slotClass .= ' is-hour';
      }
      $html .= "<div class='{$slotClass}'></div>";
    }
    $html .= "<div class='cal-day-events'>";
    foreach ($dayEntries as $entry) {
      $html .= calendarEntryBlock($entry, $rich, $span, $bounds);
    }
    // Gap overlays for visual highlight
    $dayGaps = getTimeGaps([$day => $dayEntries], 15);
    foreach ($dayGaps as $gap) {
      $geo = calendarBlockGeometry($gap['start'], $gap['end'], null, $span, $bounds);
      $html .= "<div class='cal-gap' style='top:{$geo['top']}%;height:{$geo['height']}%' title='Untracked'></div>";
    }
    $html .= "</div></div></div>";
  }

  $html .= "</div></div>";
  return $html;
}

/**
 * Full calendar home layout.
 */
function renderCalendarHome($anchorDate = null, $span = null)
{
  $span = normalizeCalendarSpan($span !== null ? $span : settingValue('calendar_span', 'week'));
  $anchorDate = $anchorDate ?: date('Y-m-d');
  if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $anchorDate)) {
    $anchorDate = date('Y-m-d');
  }

  if ($span === 'week') {
    $rangeStart = weekStartMonday($anchorDate);
    $rangeEnd = date('Y-m-d', strtotime('+6 days', strtotime($rangeStart)));
    $prevAnchor = date('Y-m-d', strtotime('-7 days', strtotime($rangeStart)));
    $nextAnchor = date('Y-m-d', strtotime('+7 days', strtotime($rangeStart)));
    $heading = date('j M', strtotime($rangeStart)) . ' – ' . date('j M Y', strtotime($rangeEnd));
  } else {
    $rangeStart = $anchorDate;
    $rangeEnd = $anchorDate;
    $prevAnchor = date('Y-m-d', strtotime('-1 day', strtotime($anchorDate)));
    $nextAnchor = date('Y-m-d', strtotime('+1 day', strtotime($anchorDate)));
    $heading = date('l j F Y', strtotime($anchorDate));
  }

  $entries = getEntriesForRange($rangeStart . ' 00:00:00', $rangeEnd . ' 23:59:59');

  $dayActive = ($span === 'day') ? ' is-active' : '';
  $weekActive = ($span === 'week') ? ' is-active' : '';

  $html = "<div class='home-calendar' id='homeCalendar'>";
  $html .= "<div class='cal-mini-tracker'>" . compactTrackerMarkup() . "</div>";

  $html .= "<div class='cal-toolbar'>";
  $html .= "<div class='cal-span-switcher' role='toolbar' aria-label='Calendar span'>";
  $html .= "<button type='button' class='home-view-btn{$dayActive}' onclick='switchCalendarSpan(\"day\")'>Day</button>";
  $html .= "<button type='button' class='home-view-btn{$weekActive}' onclick='switchCalendarSpan(\"week\")'>Week</button>";
  $html .= "</div>";
  $html .= "<div class='cal-nav'>";
  $html .= "<a class='cal-nav-btn' href='index.php?day={$prevAnchor}'>←</a>";
  $html .= "<a class='cal-nav-btn' href='index.php?day=" . date('Y-m-d') . "'>Today</a>";
  $html .= "<a class='cal-nav-btn' href='index.php?day={$nextAnchor}'>→</a>";
  $html .= "</div>";
  $html .= "<h2 class='cal-heading'>" . htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') . "</h2>";
  $html .= "</div>";

  $html .= "<div class='cal-layout'>";
  $html .= "<div class='cal-main'>" . renderCalendarGrid($anchorDate, $span, $entries) . "</div>";
  $html .= renderFollowUpsPanel($entries, $rangeStart, $rangeEnd);
  $html .= "</div>";

  $html .= "</div>";
  return $html;
}
