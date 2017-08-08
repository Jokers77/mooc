<?php

// src/OC/PlatformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Application;
use Knp\Bundle\PaginatorBundle\KnpPaginator;

class AdvertController extends Controller
{

  public function homeAction() {
    return $this->render('OCPlatformBundle:Advert:home.html.twig');
  }

  public function indexAction(Request $request)
  {
      $em = $this->getDoctrine()->getManager();
      $advert = $em->getRepository('OCPlatformBundle:Advert')->findAll();

      $paginator  = $this->get('knp_paginator');
      $result = $paginator->paginate(
          $advert,
          $request->query->getInt('page', 1),
          $request->query->getInt('limit', 5)
      );



    return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
        'result' => $result,
    ));
  }

  public function viewAction($id)
  {

    $em = $this->getDoctrine()->getManager();

    $repository = $this->getDoctrine()
        ->getManager()
        ->getRepository('OCPlatformBundle:Advert');

    // On récupère l'entité correspondante à l'id $id
    $advert = $repository->find($id);

    // $advert est donc une instance de OC\PlatformBundle\Entity\Advert
    // ou null si l'id $id  n'existe pas, d'où ce if :
    if (null === $advert) {
      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    }

    // On récupère la liste des candidatures de cette annonce
    $listApplications = $em
        ->getRepository('OCPlatformBundle:Application')
        ->findBy(array('advert' => $advert))
    ;

    // Le render ne change pas, on passait avant un tableau, maintenant un objet
    return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
        'advert' => $advert,
        'listApplications' => $listApplications
    ));
  }

  public function addAction(Request $request)
  {
    $advert = new Advert();
    $advert->setAuthor('DC Comic');
    $advert->setContent("Spiderman l'homme arraigné !");
    $advert->setTitle('QSDQSDFQSFD');

    $image = new Image();
    $image->setUrl("http://t0.gstatic.com/images?q=tbn:ANd9GcT72uKlaQ_H8LDv6-iAdjjOH_V38w7oCtRAid9d7ievuVmJLDOG");
    $image->setAlt('Spiderman');

    $application1 = new Application();
    $application1->setAuthor('Antoine');
    $application1->setContent("Je suis parfait pour ce poste.");

    $application1->setAdvert($advert);
    $advert->setImage($image);

    $em = $this->getDoctrine()->getManager();
    $em->persist($advert);
    $em->persist($application1);
    $em->flush();
    // Si la requête est en POST, c'est que le visiteur a soumis le formulaire
    if ($request->isMethod('POST')) {
      $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

      // Puis on redirige vers la page de visualisation de cettte annonce
      // return $this->redirectToRoute('oc_platform_view', array('id' => 5));
      //$antispam = $this->get('oc_platform_antispam');

      //$text = "..qsdqsdqsdsklfhjqskjdflmkqsjdmlfkjqsmlkdfjmlqksjdfmlkqjsdlmkfjqmlksdjfmlqkjsdflmkqsjhmdljkghfqkljfhglksjdhfglkjhdsqlkjghss.";

      //if($antispam->isSpam($text)) {
      //throw new \Exception('Votre message a été détecté comme spam');
      //}

      // Si on n'est pas en POST, alors on affiche le formulaire
      return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
    }

    return $this->render('OCPlatformBundle:Advert:add.html.twig', array('advert' => $advert));
  }

  public function editAction($id, Request $request)
  {

    $em = $this->getDoctrine()->getManager();

    // On récupère l'annonce $id
    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

    if (null === $advert) {
      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    }

    // La méthode findAll retourne toutes les catégories de la base de données
    $listCategories = $em->getRepository('OCPlatformBundle:Category')->findAll();

    // On boucle sur les catégories pour les lier à l'annonce
    foreach ($listCategories as $category) {
      $advert->addCategory($category);
    }

    // Pour persister le changement dans la relation, il faut persister l'entité propriétaire
    // Ici, Advert est le propriétaire, donc inutile de la persister car on l'a récupérée depuis Doctrine

    // Étape 2 : On déclenche l'enregistrement
    $em->flush();


    if ($request->isMethod('POST')) {
      $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');

      return $this->redirectToRoute('oc_platform_view', array('id' => 5));
    }

    $advert = array(
      'title'   => 'Recherche développpeur Symfony',
      'id'      => $id,
      'author'  => 'Alexandre',
      'content' => 'Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…',
      'date'    => new \Datetime()
    );

    return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
      'advert' => $advert
    ));
  }

  public function deleteAction($id)
  {
    return $this->render('OCPlatformBundle:Advert:delete.html.twig');
  }

  public function menuAction($limit)
  {

    $repository = $this->getDoctrine()->getManager()->getRepository('OCPlatformBundle:Advert');
    $listAdverts = $repository->findBy(array(),array(), $limit = 5);

    return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
      // Tout l'intérêt est ici : le contrôleur passe les variables nécessaires au template !
      'listAdverts' => $listAdverts
    ));
  }
}
