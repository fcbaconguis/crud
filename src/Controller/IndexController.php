<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class IndexController extends Controller {

    /**
      * @Route("/")
      */

	public function number()
    {
        echo phpinfo();
    }

}