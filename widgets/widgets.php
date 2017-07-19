<?php
/**
 * This file loads all the plugin's Widgets (and their base classes).
 * The order of these lines matters. :P
 *
 * @package Plugin
 */
// Base widget class.
require_once dirname(__FILE__) . '/WPFetLifeWidget.php';
// Profile widgets.
require_once dirname(__FILE__) . '/WPFetLifeProfileWidget.php';
require_once dirname(__FILE__) . '/WPFetLifeProfileEventsWidget.php';
require_once dirname(__FILE__) . '/WPFetLifeProfileGroupsWidget.php';
// Event widgets.
require_once dirname(__FILE__) . '/WPFetLifeEventWidget.php';
require_once dirname(__FILE__) . '/WPFetLifeEventParticipantsWidget.php';
