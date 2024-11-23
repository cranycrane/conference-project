<?php

namespace App\UI\Accessory;


/**
 * Trait to enforce user authentication.
 * Redirects unauthenticated users to the sign-in page.
 */
trait RequireLoggedUser
{
  public function injectRequireLoggedUser(): void
  {
    $this->onStartup[] = function () {
      $user = $this->getUser();
      if ($user->isLoggedIn()) {
        return;
      } elseif ($user->getLogoutReason() === $user::LogoutInactivity) {
        $this->flashMessage('Z důvodu neaktivity jste byl/a odhlášen/a. Přihlaste se, prosím, znovu.');
        $this->redirect(':Front:Sign:in', ['backlink' => $this->storeRequest()]);
      } else {
        $this->redirect(':Front:Sign:in');
      }
    };
  }
}
