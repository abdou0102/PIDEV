<?php

namespace BiblioBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use BiblioBundle\Entity\Livres;
use BiblioBundle\Form\LivresType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class LivresController extends Controller
{
    public function AjoutLivreAction(Request $request)
    {
        $user=$this->getUser();
        if($user != null)
        {
            if($user->getRoles()[0]=="ADMIN"){
                //1-form
                //1-a:objet vide
                $livre=new Livres();
                //1-b:create form
                $form=$this->createForm(LivresType::class,$livre)
                    ->add('image',FileType::class,array('label'=>'insert image'));
                //2-les données
                $form=$form->handleRequest($request);
                $nom=$livre->getNom();
                $auteur=$livre->getAuteur();
                $quantite=$livre->getQuantite();
                $description=$livre->getDescription();

                if($form->isSubmitted())
                {

                    //$valid =1;
                    //die('here');
                    /**
                     * @var UploadedFile $file
                     */
                    $file=$livre->getImage();
                    $fileName=md5(uniqid()).'.'.$file->guessExtension();
                    $file->move($this->getParameter('image_directory'),$fileName);
                    $livre->setImage($fileName);
                    //3-cnx avec BD
                    $em=$this->getDoctrine()->getManager();
                    $em->persist($livre);
                    $em->flush();
                    return $this->redirectToRoute('AfficheLivre');
                }

                //1-c:envoi du form
                return $this->render('@Biblio/Livres/AjoutLivre.html.twig', array(
                    "f"=>$form->createView()
                ));
            }
            else{
                return $this->redirectToRoute("fos_user_security_login");

            }
        }

        else{
            return $this->redirectToRoute("fos_user_security_login");

        }
    }

    public function AfficheLivreAction(Request $request)
    {
        //les donnée de bdd
        $livre=$this->getDoctrine()
            ->getRepository(Livres::class)
            ->findAll();
        //pagination
       /* $pagination  = $this->get('knp_paginator')->paginate(
            $livre,
            $request->query->get('page', 1) le numéro de la page à afficher,
            3 nbre d'éléments par page*/
        //affichage
        return $this->render("@Biblio/Livres/AfficheLivre.html.twig", array("list"=>$livre));
    }

    function UpdateLivreAction(Request $request,$id){
        $em=$this->getDoctrine()->getManager();
        $livre=$this->getDoctrine()
            ->getRepository(Livres::class)
            ->find($id);
        $Form=$this->createForm(LivresType::class,$livre);
        $Form->handleRequest($request);

        if($Form->isSubmitted()){
            $em->flush();
            return $this->redirectToRoute('AfficheLivre');

        }
        return $this->render('@Biblio/Livres/UpdateLivre.html.twig',
            array('f'=>$Form->createView()));
    }

    function DeleteLivreAction($id){
        $em=$this->getDoctrine()->getManager();
        $livre=$this->getDoctrine()->getRepository(Livres::class)
            ->find($id);
        $em->remove($livre);
        $em->flush();
        return $this->redirectToRoute('AfficheLivre');
    }
}
