<?php
/**
 * This file is part of OpenClinic
 *
 * Copyright (c) 2002-2004 jact
 * Licensed under the GNU GPL. For full terms see the file LICENSE.
 *
 * $Id: user_access_log.php,v 1.3 2004/06/07 18:33:04 jact Exp $
 */

/**
 * user_access_log.php
 ********************************************************************
 * List of user's accesses
 ********************************************************************
 * Author: jact <jachavar@terra.es>
 */

  ////////////////////////////////////////////////////////////////////
  // Controlling vars
  ////////////////////////////////////////////////////////////////////
  $tab = "admin";
  $nav = "users";
  $restrictInDemo = true; // There are not logs in demo version
  $returnLocation = "../admin/user_list.php";

  ////////////////////////////////////////////////////////////////////
  // Checking for get vars. Go back to form if none found.
  ////////////////////////////////////////////////////////////////////
  if (count($_GET) == 0)
  {
    header("Location: " . $returnLocation);
    exit();
  }

  ////////////////////////////////////////////////////////////////////
  // Retrieving get vars
  ////////////////////////////////////////////////////////////////////
  $idUser = intval($_GET["key"]);
  $login = $_GET["login"];

  require_once("../shared/read_settings.php");
  require_once("../shared/login_check.php");
  require_once("../classes/Access_Query.php");
  require_once("../lib/error_lib.php");
  require_once("../lib/input_lib.php");
  require_once("../lib/search_lib.php");

  ////////////////////////////////////////////////////////////////////
  // Retrieving post vars and scrubbing the data
  ////////////////////////////////////////////////////////////////////
  if (isset($_POST["page"]))
  {
    $currentPageNmbr = $_POST["page"];
  }
  else
  {
    $currentPageNmbr = 1;
  }
  $limit = $_POST["limit"];

  ////////////////////////////////////////////////////////////////////
  // Search user accesses
  ////////////////////////////////////////////////////////////////////
  $accessQ = new Access_Query();
  $accessQ->setItemsPerPage(OPEN_ITEMS_PER_PAGE);
  $accessQ->connect();
  if ($accessQ->errorOccurred())
  {
    $accessQ->close();
    showQueryError($accessQ);
  }

  if ( !$accessQ->searchUser($idUser, $currentPageNmbr, $limit) )
  {
    $accessQ->close();
    showQueryError($accessQ);
  }

  ////////////////////////////////////////////////////////////////////
  // Show success page
  ////////////////////////////////////////////////////////////////////
  $title = _("Access Logs");
  require_once("../shared/header.php");

  ////////////////////////////////////////////////////////////////////
  // Navigation links
  ////////////////////////////////////////////////////////////////////
  require_once("../shared/navigation_links.php");
  $links = array(
    _("Admin") => "../admin/index.php",
    _("Users") => $returnLocation,
    $title => ""
  );
  showNavLinks($links, "users.png");
  unset($links);

  echo '<h3>' . sprintf(_("Access Logs List for user %s"), $login) . "</h3>\n";

  if ($accessQ->getRowCount() == 0)
  {
    $accessQ->close();
    echo '<p>' . _("No logs for this user.") . "</p>\n";
  }
  else
  {
    // Printing result stats and page nav
    echo '<p><strong>' . sprintf(_("%d accesses."), $accessQ->getRowCount()) . "</strong></p>\n";

    $pageCount = $accessQ->getPageCount();
    showResultPages($currentPageNmbr, $pageCount);
?>

<!-- JavaScript to post back to this page -->
<script type="text/javascript">
<!--
function changePage(page)
{
  document.forms[0].page.value = page;
  document.forms[0].submit();

  return false;
}
//-->
</script>

<!-- Form used by javascript to post back to this page -->
<form method="post" action="../admin/user_access_log.php?key=<?php echo $idUser; ?>&amp;login=<?php echo $login; ?>">
  <div>
<?php
  showInputHidden("page", $currentPageNmbr);
  showInputHidden("limit", $_POST["limit"]);
?>
  </div>
</form>

<table>
  <thead>
    <tr>
      <th>
        <?php echo _("Access Date"); ?>
      </th>

      <th>
        <?php echo _("Login"); ?>
      </th>

      <th>
        <?php echo _("Profile"); ?>
      </th>
    </tr>
  </thead>

  <tbody>
<?php
    $rowClass = "odd";
    while ($access = $accessQ->fetchAccess())
    {
      echo '<tr class="' . $rowClass . '">';
      echo '<td>' . localDate($access["access_date"]) . "</td>\n";
      echo '<td class="center">' . $access["login"] . "</td>\n";
      echo '<td class="center">' . $access["profile"] . "</td>\n";
      echo "</tr>\n";
      // swap row color
      ($rowClass == "even") ? $rowClass = "odd" : $rowClass = "even";
    }
    $accessQ->freeResult();
    $accessQ->close();
?>
  </tbody>
</table>
<?php
    showResultPages($currentPageNmbr, $pageCount);
  } // end if-else
  unset($accessQ);

  echo '<p><a href="' . $returnLocation . '">' . _("Return to users list") . "</a></p>\n";

  require_once("../shared/footer.php");
?>