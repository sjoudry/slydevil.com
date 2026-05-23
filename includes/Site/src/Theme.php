<?php

namespace SlyDevil\Site;

use SlyDevil\Form\Utility\SessionManager;

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

  protected ?Login $login = NULL;

  public function __construct(Login $login) {
    $this->login = $login;
  }

  public function htmlDashboardBottom() {
    $html = '';

    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div class="footer-container">';
    $html .= '<div class="footer-wrapper">';
    $html .= '<div class="footer">&copy 1999-' . date('Y') . ' Sly Devil</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</body>';
    $html .= '</html>';

    return $html;
  }

  public function htmlDashboardMenu() {
    $html = '';

    $html .= '<div class="menu-container">';
    $html .= '<div class="menu-wrapper">';
    $html .= '<div class="menu">';
    $html .= '<ul>';

    foreach (self::MENU as $link) {
      if ($this->login->getUserId() == 1 && isset($link['hide'])) {
        continue;
      }

      if ($this->login->checkPermissions($link['perm']) || $link['perm'] == 'logout') {
        $html .= '<li class="' . $link['perm'];
        if ($link['label'] == 'Home') {
          if ($_SERVER['REQUEST_URI'] == '/dashboard/') {
            $html .= ' active';
          }
        }
        else {
          if (stripos($_SERVER['REQUEST_URI'], $link['url']) !== FALSE) {
            $html .= ' active';
          }
        }
        $html .= '"><a href="' . $link['url'] . '">' . $link['label'] . '</a></li>';
      }
    }

    $html .= '</ul>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';

    return $html;
  }

  public function htmlDashboardTop(string $title, bool $show_menu = TRUE) {
    $html  = '';

    $html .= '<html>';
    $html .= '<head>';
    $html .= '<title>' . $title . '</title>';
    $html .= '<link href="/includes/css/admin.css" media="screen" rel="stylesheet" type="text/css" />';
    $html .= '<link href="/includes/css/bootstrap.min.css" rel="stylesheet">';
    $html .= '<link href="/includes/css/font-awesome.css" rel="stylesheet">';
    $html .= '</head>';
    $html .= '<body>';
    $html .= '<div class="page-container">';
    $html .= '<div class="page-wrapper">';
    $html .= '<div class="page">';
    $html .= '<div class="logo-container">';
    $html .= '<div class="logo-wrapper">';
    $html .= '<div class="logo"><a href="/">Logo</a></div>';
    $html .= '</div>';
    $html .= '</div>';

    if ($show_menu) {
      $html .= $this->htmlDashboardMenu();
    }

    $typed_messages = $this->login->getSessionManager()->getMessages();
    if (count($typed_messages)) {
      foreach ($typed_messages as $type => $messages) {
        $html .= '<div class="messages-' . $type . '-container">';
        $html .= '<div class="messages-' . $type . '-wrapper">';
        $html .= '<div class="messages-' . $type . '">';
        $html .= '<ul>';

        foreach ($messages as $message) {
          $html .= '<li>' . $message . '</li>';
        }

        $html .= '</ul>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
      }
    }

    return $html;
  }

  public function htmlInvoiceBottom() {
    $html = '';

    $html .= "</div>\n";
    $html .= "</div>\n";
    $html .= "</div>\n";
    $html .= "</body>\n";
    $html .= "</html>\n";

    return $html;
  }

  public function htmlInvoiceTop(string $title) {
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

  public function htmlLoginBottom() {
    $html = '';

    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= $this->htmlDashboardBottom();

    return $html;
  }

  public function htmlLoginTop(string $title) {
    $html = '';

    $html .= $this->htmlDashboardTop($title, FALSE);
    $html .= '<div class="login-container">';
    $html .= '<div class="login-wrapper">';
    $html .= '<div class="login">';

    return $html;
  }

}