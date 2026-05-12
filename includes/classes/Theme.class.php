<?php

namespace SlyDevil;

class Theme {

  public const MENU = [
    // User:
    [
      "url"   => "/dashboard/",
      "label" => "Home",
      "perm"  => "user",
      "hide"  => TRUE,
    ],
    [
      "url"   => "/dashboard/details.php",
      "label" => "Account Details",
      "perm"  => "user",
      "hide"  => TRUE,
    ],
      
    // Admin:
    [
      "url"   => "/dashboard/users/",
      "label" => "Users",
      "perm"  => "admin",
    ],
    [
      "url"   => "/dashboard/packages/",
      "label" => "Packages",
      "perm"  => "admin",
    ],
    [
      "url"   => "/dashboard/accounts/",
      "label" => "Accounts",
      "perm"  => "admin",
    ],
    [
      "url"   => "/dashboard/domains/",
      "label" => "Domains",
      "perm"  => "admin",
    ],
    [
      "url"   => "/dashboard/services/",
      "label" => "Services",
      "perm"  => "admin",
    ],
    [
      "url"   => "/dashboard/invoices/",
      "label" => "Invoices",
      "perm"  => "admin",
    ],
    [
      "url"   => "/login/logout.php",
      "label" => "Logout",
      "perm"  => "logout",
    ],
  ];

  public static function htmlDashboardBottom() {
    $html = '';
    
    $html .= "</div>\n";
    $html .= "</div>\n";
    $html .= "</div>\n";
    $html .= "<div class='footer-container'>\n";
    $html .= "<div class='footer-wrapper'>\n";
    $html .= "<div class='footer'>";
    $html .= "&copy 1999-" . date("Y") . " Sly Devil";
    $html .= "</div>\n";
    $html .= "</div>\n";
    $html .= "</div>\n";
    $html .= "</body>\n";
    $html .= "</html>\n";
    
    return $html;
  }

  public static function htmlDashboardMenu() {
    $html = '';
    
    $html .= "<div class='menu-container'>\n";
    $html .= "<div class='menu-wrapper'>\n";
    $html .= "<div class='menu'>\n";
    $html .= "<ul>\n";

    foreach (self::MENU as $link) {
      if (Login::$userId == 1 && isset($link['hide'])) {
        continue;
      }

      if (Login::checkPermissions($link['perm']) || $link['perm'] == 'logout') {
        $html .= "<li class='" . $link["perm"];
        if ($link['label'] == 'Home') {
          if ($_SERVER['REQUEST_URI'] == '/dashboard/') {
            $html .= " active";
          }
        }
        else {
          if (stripos($_SERVER['REQUEST_URI'], $link['url']) !== FALSE) {
            $html .= " active";
          }
        }
        $html .= "'><a href='" . $link["url"] . "'>" . $link["label"] . "</a></li>\n";
      }
    }
    
    $html .= "</ul>\n";
    $html .= "</div>\n";
    $html .= "</div>\n";
    $html .= "</div>\n";

    return $html;
  }

  public static function htmlDashboardTop(string $title, bool $show_menu = TRUE) {
    $html  = '';
    
    $html .= "<html>\n";
    $html .= "<head>";
    $html .= "<title>" . $title . "</title>";
    $html .= "<link href='/includes/css/admin.css' media='screen' rel='stylesheet' type='text/css' />\n";
    $html .= "<link href='//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css' rel='stylesheet'>\n";
    $html .= "<link href='//netdna.bootstrapcdn.com/font-awesome/4.0.0/css/font-awesome.css' rel='stylesheet'>\n";
    $html .= "</head>\n";
    $html .= "<body>\n";
    $html .= "<div class='page-container'>\n";
    $html .= "<div class='page-wrapper'>\n";
    $html .= "<div class='page'>\n";
    $html .= "<div class='logo-container'>\n";
    $html .= "<div class='logo-wrapper'>\n";
    $html .= "<div class='logo'><a href='/'>Logo</a></div>\n";
    $html .= "</div>\n";
    $html .= "</div>\n";
    
    if ($show_menu) {
      $html .= self::htmlDashboardMenu();
    }
    
    if (isset($_SESSION['messages']['info']) && count($_SESSION['messages']['info']) > 0) {
      $html .= "<div class='messages-info-container'>\n";
      $html .= "<div class='messages-info-wrapper'>\n";
      $html .= "<div class='messages-info'>\n";
      $html .= "<ul>\n";
            
      foreach ($_SESSION['messages']['info'] as $message) {
        $html .= "<li>" . $message . "</li>\n";
      }
            
      $html .= "</ul>\n";
      $html .= "</div>\n";
      $html .= "</div>\n";
      $html .= "</div>\n";
            
      $_SESSION["messages"]["info"] = [];
    }
    
    return $html;
  }

  public static function htmlInvoiceBottom() {
    $html = '';
      
    $html .= "</div>\n";
    $html .= "</div>\n";
    $html .= "</div>\n";
    $html .= "</body>\n";
    $html .= "</html>\n";
    
    return $html;
  }

  public static function htmlInvoiceTop(string $title) {
    $html  = '';
    
    $html .= "<html>\n";
    $html .= "<head>";
    $html .= "<title>" . $title . "</title>";
    $html .= "<link href='/includes/css/admin.css' media='screen' rel='stylesheet' type='text/css' />\n";
    $html .= "<link href='//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css' rel='stylesheet'>\n";
    $html .= "<link href='//netdna.bootstrapcdn.com/font-awesome/4.0.0/css/font-awesome.css' rel='stylesheet'>\n";
    $html .= "</head>\n";
    $html .= "<body>\n";
    $html .= "<div class='page-container'>\n";
    $html .= "<div class='page-wrapper'>\n";
    $html .= "<div class='page'>\n";
    
    return $html;
  }

  public static function htmlLoginBottom() {
    $html = '';
    
    $html .= "</div>\n";
    $html .= "</div>\n";
    $html .= "</div>\n";
    $html .= self::htmlDashboardBottom();
    
    return $html;
  }

  public static function htmlLoginTop(string $title) {
    $html = '';

    $html .= self::htmlDashboardTop($title, FALSE);
    $html .= "<div class='login-container'>\n";
    $html .= "<div class='login-wrapper'>\n";
    $html .= "<div class='login'>\n";
    
    return $html;
  }

}