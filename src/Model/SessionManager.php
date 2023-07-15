<?php

namespace Model;

class SessionManager
{

  public function startSession(): void
  {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  }


  public function destroySession(): void
  {

    if (session_status() === PHP_SESSION_ACTIVE) {
      session_destroy();
    }
  }
}
