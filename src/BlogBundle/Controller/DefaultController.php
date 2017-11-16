<?php

namespace BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

use BlogBundle\Entity\Post;
use BlogBundle\Form\PostType;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function indexAction()
    {
        return $this->render('BlogBundle:Default:index.html.twig');
    }

    /**
     * @Route("/post/create", name="post_create")
     */
    public function createPostAction(Request $request)
    {
        $post = new Post();
        $post->setTitle('Default Title');
        $form = $this->createForm(PostType::class, $post);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();
            
            return $this->redirectToRoute('home');
        }
        
        return $this->render('BlogBundle:Post:create.html.twig',
            ['form' => $form->createView()]
        );
    }
}

