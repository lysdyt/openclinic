<?php
/**
 * LogStats.php
 *
 * Contains the class LogStats
 *
 * Licensed under the GNU GPL. For full terms see the file LICENSE.
 *
 * @package   OpenClinic
 * @copyright 2002-2007 jact
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @version   CVS: $Id: LogStats.php,v 1.1 2007/01/29 16:02:13 jact Exp $
 * @author    jact <jachavar@gmail.com>
 * @todo static class
 */

  require_once("../model/LogStats_Query.php");
  require_once("../lib/HTML.php");

/**
 * LogStats set of log stats functions
 *
 * Methods:
 *  string _percBar(int $percentage, int $scale = 1, string $label = "")
 *  void yearly(string $table)
 *  void monthly(string $table, int $year)
 *  void daily(string $table, int $year, int $month)
 *  void hourly(string $table, int $year, int $month, int $day)
 *  void all(string $table)
 *  void links(string $table)
 *
 * @package OpenClinic
 * @author jact <jachavar@gmail.com>
 * @access public
 * @since 0.8
 */
class LogStats
{
  /*
   * string _percBar(int $percentage, int $scale = 1, string $label = "")
   *
   * Returns a percentage bar
   *
   * @param int $percentage
   * @param int $scale (optional)
   * @param string $label (optional) alternative text of the images
   * @return string
   * @access private
   */
  function _percBar($percentage, $scale = 1, $label = "")
  {
    //$leftSize = getimagesize("../img/leftbar.gif");
    //$mainSize = getimagesize("../img/mainbar.gif");
    //$rightSize = getimagesize("../img/rightbar.gif");

    $perc = round($scale * $percentage, 0);

    $html = HTML::strStart('img',
      array(
        'src' => '../img/leftbar.gif',
        'width' => 7,
        'height' => 14,
        'alt' => $label
      ),
      true
    );
    $html .= HTML::strStart('img',
      array(
        'src' => '../img/mainbar.gif',
        'width' => $perc,
        'height' => 14,
        'alt' => $label
      ),
      true
    );
    $html .= HTML::strStart('img',
      array(
        'src' => '../img/rightbar.gif',
        'width' => 7,
        'height' => 14,
        'alt' => $label
      ),
      true
    );
    $html = str_replace("\n", '', $html);

    return $html;
  }

  /*
   * void yearly(string $table)
   *
   * Draws a table with yearly stats
   *
   * @param string $table
   * @return void
   * @access public
   */
  function yearly($table)
  {
    $logQ = new LogStats_Query($table);
    $logQ->connect();

    $totalHits = $logQ->totalHits();

    HTML::section(4, sprintf(_("Yearly Stats: %d hits"), $totalHits));

    if ($totalHits == 0)
    {
      $logQ->close();
      HTML::message(_("There are not statistics"), OPEN_MSG_INFO);

      return;
    }

    $thead = array(
      _("Year"),
      _("Hits")
    );

    $options = array(
      //'align' => 'center',
      1 => array('nowrap' => 1)
    );

    $array = $logQ->hitsByYear();

    $tbody = array();
    foreach ($array as $year => $hits)
    {
      $row = HTML::strLink($year, $_SERVER['PHP_SELF'],
        array(
          'table' => $table,
          'option' => 'monthly',
          'year' => $year
        )
      );
      $row .= OPEN_SEPARATOR;
      $widthImage = round(100 * $hits / $totalHits, 0);
      $percent = substr(100 * $hits / $totalHits, 0, 5);
      $row .= LogStats::_percBar($widthImage);
      $row .= ' ' . $percent . '% (' . HTML::strLink($hits, '../admin/log_' . $table . '_list.php', array('year' => $year)) . ')';

      $tbody[] = explode(OPEN_SEPARATOR, $row);
    }
    HTML::table($thead, $tbody, null, $options);

    $logQ->freeResult();
    $logQ->close();
    unset($logQ);
  }

  /*
   * void monthly(string $table, int $year)
   *
   * Draws a table with monthly stats
   *
   * @param string $table
   * @param int $year
   * @return void
   * @access public
   */
  function monthly($table, $year)
  {
    $logQ = new LogStats_Query($table);
    $logQ->connect();

    $totalHits = $logQ->yearHits($year);

    HTML::section(4, sprintf(_("Monthly Stats for %d: %d hits"), intval($year), $totalHits));

    if ($totalHits == 0)
    {
      $logQ->close();
      HTML::message(_("There are not statistics"), OPEN_MSG_INFO);

      return;
    }

    $thead = array(
      _("Month"),
      _("Hits")
    );

    $options = array(
      //'align' => 'center',
      1 => array('nowrap' => 1)
    );

    $array = $logQ->yearHitsByMonth($year);

    $months = array(
      _("January"),
      _("February"),
      _("March"),
      _("April"),
      _("May"),
      _("June"),
      _("July"),
      _("August"),
      _("September"),
      _("October"),
      _("November"),
      _("December")
    );

    $tbody = array();
    foreach ($array as $month => $hits)
    {
      $row = HTML::strLink($months[intval($month) - 1], $_SERVER['PHP_SELF'],
        array(
          'table' => $table,
          'option' => 'daily',
          'year' => $year,
          'month' => $month
        )
      );
      $row .= OPEN_SEPARATOR;
      $widthImage = round(100 * $hits / $totalHits, 0);
      $percent = substr(100 * $hits / $totalHits, 0, 5);
      $row .= LogStats::_percBar($widthImage);
      $row .= ' ' . $percent . '% (' . HTML::strLink($hits, '../admin/log_' . $table . '_list.php',
        array(
          'year' => $year,
          'month' => $month
        )
      ) . ')';

      $tbody[] = explode(OPEN_SEPARATOR, $row);
    }
    HTML::table($thead, $tbody, null, $options);

    $logQ->freeResult();
    $logQ->close();
    unset($logQ);
  }

  /*
   * void daily(string $table, int $year, int $month)
   *
   * Draws a table with daily stats
   *
   * @param string $table
   * @param int $year
   * @param int $month
   * @return void
   * @access public
   */
  function daily($table, $year, $month)
  {
    $logQ = new LogStats_Query($table);
    $logQ->connect();

    $totalHits = $logQ->monthHits($year, $month);

    $months = array(
      _("January"),
      _("February"),
      _("March"),
      _("April"),
      _("May"),
      _("June"),
      _("July"),
      _("August"),
      _("September"),
      _("October"),
      _("November"),
      _("December")
    );

    HTML::section(4, sprintf(_("Daily Stats for %s, %d: %d hits"), $months[intval($month) - 1], intval($year), $totalHits));

    if ($totalHits == 0)
    {
      $logQ->close();
      HTML::message(_("There are not statistics"), OPEN_MSG_INFO);

      return;
    }

    $thead = array(
      _("Day"),
      _("Hits")
    );

    $options = array(
      //'align' => 'center',
      0 => array('align' => 'right'),
      1 => array('nowrap' => 1)
    );

    $array = $logQ->monthHitsByDay($year, $month);

    $tbody = array();
    foreach ($array as $day => $hits)
    {
      $row = HTML::strLink(intval($day), $_SERVER['PHP_SELF'],
        array(
          'table' => $table,
          'option' => 'hourly',
          'year' => $year,
          'month' => $month,
          'day' => $day
        )
      );
      $row .= OPEN_SEPARATOR;
      if ($hits == 0)
      {
        $widthImage = 0;
        $percent = 0;
      }
      else
      {
        $widthImage = round(100 * $hits / $totalHits, 0);
        $percent = substr(100 * $hits / $totalHits, 0, 5);
        $hits = HTML::strLink($hits, '../admin/log_' . $table . '_list.php',
          array(
            'year' => $year,
            'month' => $month,
            'day' => $day
          )
        );
      }
      $row .= LogStats::_percBar($widthImage);
      $row .= ' ' . $percent . '% (' . $hits . ')';

      $tbody[] = explode(OPEN_SEPARATOR, $row);
    }
    HTML::table($thead, $tbody, null, $options);

    $logQ->freeResult();
    $logQ->close();
    unset($logQ);
  }

  /*
   * void hourly(string $table, int $year, int $month, int $day)
   *
   * Draws a table with hourly stats
   *
   * @param string $table
   * @param int $year
   * @param int $month
   * @param int $day
   * @return void
   * @access public
   */
  function hourly($table, $year, $month, $day)
  {
    $logQ = new LogStats_Query($table);
    $logQ->connect();

    $totalHits = $logQ->dayHits($year, $month, $day);

    $months = array(
      _("January"),
      _("February"),
      _("March"),
      _("April"),
      _("May"),
      _("June"),
      _("July"),
      _("August"),
      _("September"),
      _("October"),
      _("November"),
      _("December")
    );

    HTML::section(4, sprintf(_("Hourly Stats for %s %d, %d: %d hits"), $months[intval($month) - 1], intval($day), intval($year), $totalHits));

    if ($totalHits == 0)
    {
      $logQ->close();
      HTML::message(_("There are not statistics"), OPEN_MSG_INFO);

      return;
    }

    $array = $logQ->dayHitsByHour($year, $month, $day);

    $thead = array(
      _("Hour"),
      _("Hits")
    );

    $options = array(
      //'align' => 'center',
      1 => array('nowrap' => 1)
    );

    $tbody = array();
    foreach ($array as $hour => $hits)
    {
      $row = sprintf("%02d:00 - %02d:59", $hour, $hour);
      $row .= OPEN_SEPARATOR;
      if ($hits == 0)
      {
        $widthImage = 0;
        $percent = 0;
      }
      else
      {
        $widthImage = round(100 * $hits / $totalHits, 0);
        $percent = substr(100 * $hits / $totalHits, 0, 5);
        $hits = HTML::strLink($hits, '../admin/log_' . $table . '_list.php',
          array(
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'hour', $hour
          )
        );
      }
      $row .= LogStats::_percBar($widthImage);
      $row .= ' ' . $percent . '% (' . $hits . ')';

      $tbody[] = explode(OPEN_SEPARATOR, $row);
    }
    HTML::table($thead, $tbody, null, $options);

    $logQ->freeResult();
    $logQ->close();
    unset($logQ);
  }

  /*
   * void all(string $table)
   *
   * Draws tables with all stats
   *
   * @param string $table
   * @return void
   * @access public
   */
  function all($table)
  {
    $logQ = new LogStats_Query($table);
    $logQ->connect();

    $total = $logQ->totalHits();
    if ($total == 0)
    {
      $logQ->close();
      HTML::message(_("There are not statistics"), OPEN_MSG_INFO);

      return;
    }

    $today = date("Y-m-d"); // calculated date
    $arrToday = explode("-", $today);

    $sectionTitle = "";
    switch ($table)
    {
      case "access":
        $sectionTitle .= _("Access Logs");
        break;

      case "record":
        $sectionTitle .= _("Record Logs");
        break;
    }
    $sectionTitle .= ': ' . $total . ' ' . strtolower(_("Hits"));
    HTML::section(3, $sectionTitle);

    $array = $logQ->busiestYear();
    if (is_array($array))
    {
      list($year, $hits) = $array;

      HTML::para(sprintf(_("Busiest Year: %d (%d hits)"), intval($year), $hits));
    }

    $array = $logQ->busiestMonth();
    if (is_array($array))
    {
      list($year, $month, $hits) = $array;

      $months = array(
        _("January"),
        _("February"),
        _("March"),
        _("April"),
        _("May"),
        _("June"),
        _("July"),
        _("August"),
        _("September"),
        _("October"),
        _("November"),
        _("December")
      );

      HTML::para(sprintf(_("Busiest Month: %s %d (%d hits)"), $months[intval($month) - 1], intval($year), $hits));
    }

    $array = $logQ->busiestDay();
    if (is_array($array))
    {
      list($year, $month, $day, $hits) = $array;

      HTML::para(sprintf(_("Busiest Day: %d %s %d (%d hits)"), intval($day), $months[intval($month) - 1], intval($year), $hits));
    }

    $array = $logQ->busiestHour();
    if (is_array($array))
    {
      list($year, $month, $day, $hour, $hits) = $array;

      $hour = sprintf("%02d:00 - %02d:59", $hour, $hour);
      HTML::para(sprintf(_("Busiest Hour: %s on %s %d, %d (%d hits)"), $hour, $months[intval($month) - 1], intval($day), intval($year), $hits));
    }

    $logQ->freeResult();
    $logQ->close();
    unset($logQ);

    HTML::rule();
    LogStats::yearly($table);
    HTML::rule();
    LogStats::monthly($table, intval($arrToday[0]));
    HTML::rule();
    LogStats::daily($table, intval($arrToday[0]), intval($arrToday[1]));
    HTML::rule();
    LogStats::hourly($table, intval($arrToday[0]), intval($arrToday[1]), $arrToday[2]);
  }

  /*
   * void links(string $table)
   *
   * Displays navigation log links
   *
   * @param string $table
   * @return void
   * @access public
   */
  function links($table)
  {
    HTML::para(
      HTML::strLink(_("Back to Main Statistics"), $_SERVER['PHP_SELF'], array('table' => $table))
      . ' | '
      . HTML::strLink(_("Back return"), (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : '../index.php'))
    );
  }
} // end class
?>