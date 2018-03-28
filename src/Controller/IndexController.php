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
        /*
        $number = mt_rand(0, 100);

        return $this->render('index/index.html.twig', array(
            'number' => $number,
        ));*/
    }

}