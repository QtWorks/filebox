<?php
// src/Controller/UserController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Package;
use App\Entity\User;
use App\Entity\Association;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;


class UserController extends AbstractController
{
        /**
         * @Route("/api/connexion", name="api_connexion")
         */
        public function api_connexion(Request $request,Session $session)
        {
          $session->set('isValid', 'false');
          $email = $request->query->get('email');
          $mdp = $request->query->get('pass');

          $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['mail' => $email]);
          $res = false;
          // echo password_hash(md5("test"), PASSWORD_BCRYPT);
          $session->set('qui', 'nope');
          $session->set('typeCompte', 'nope');

          if(password_verify($mdp,$user->getMdp()))
          {
              $res = true;

              $session->set('isValid', 'true');
              $session->set('qui', $user->getId());
              $session->set('typeCompte', 'user');
          }

          $response = new JsonResponse();

          $json = stripslashes(json_encode($res));
          $response = JsonResponse::fromJsonString($json);

          return $response;
      }

      /**
       * @Route("/verifier/connexion", name="verifier_connexion")
       */
      public function verifier_connexion(Request $request)
      {
        $session = new Session();
        $session->start();
        $session->set('isValid', 'false');
        
        $email = $request->request->get('email');
        $mdp = $request->request->get('pass');
        $res = "false";


        $session->set('qui', 'nope');
        $session->set('typeCompte', 'nope');

        if($this->getDoctrine()->getRepository(User::class)->findOneBy(['mail' => $email,'mdp' => $mdp]))
        {
            $res = "true";

            $session->set('isValid', 'true');
            $session->set('qui', $this->getDoctrine()->getRepository(User::class)->findOneBy(['mail' => $email,'mdp' => $mdp])->getId());
            $session->set('typeCompte', 'user');

            return $this->redirectToRoute('index');
        }

        return new Response('User : '.$res);
    }

     /**
       * @Route("/new", name="new")
       */

     public function new(Request $request)
     {
        return $this->render('new_compte.html.twig', [
        ]);
    }

     /**
       * @Route("/new/user", name="new_user")
       */

     public function new_user(Request $request)
     {
        if ($request->request->get('email') && $request->request->get('pass1') && $request->request->get('pass2') && $request->request->get('nom') && $request->request->get('prenom')) {

            $entityManager = $this->getDoctrine()->getManager();
            $user = new User();
            $user->setNom($request->request->get('nom'));
            $user->setPrenom($request->request->get('prenom'));
            $user->setMail($request->request->get('email'));
            $user->setMdp(password_hash(md5($request->request->get('pass1')), PASSWORD_BCRYPT));

            // tell Doctrine you want to (eventually) save the Product (no queries yet)
            $entityManager->persist($user);

            // actually executes the queries (i.e. the INSERT query)
            $entityManager->flush();

            return new Response('Nouveau User avec lid : '.$user->getId());
        }
        else 
        {
            return $this->render('bas_pas_Co.html.twig', [
            ]);
        }
    }
}