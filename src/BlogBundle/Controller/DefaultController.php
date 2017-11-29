<?php

namespace BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use BlogBundle\Entity\Post;
use BlogBundle\Form\PostType;
use BlogBundle\Entity\Comment;
use BlogBundle\Form\CommentType;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function indexAction()
    {
        $repository = $this->getDoctrine()->getRepository('BlogBundle:Post');
        $posts = $repository->findBy([], ['created' => 'DESC']);
        
        return $this->render('BlogBundle:Default:index.html.twig',
            ['posts' => $posts]
        );
    }
    
    /**
     * @Route("/delete-comment/{id}", name="delete_comment")
     * @Method({"DELETE"})
     */
    public function deleteComment(Request $request, Comment $comment)
    {
        $form = $this->createDeleteForm($comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $em->remove($comment);
            $em->flush();
        }
        
        $referer = $request->headers->get('referer');
        return new RedirectResponse($referer);
    }
    
    private function createDeleteForm(Comment $comment)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('delete_comment', array('id' => $comment->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
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
    
    /**
     * @Route("/post/{id}", name="post_view")
     */
    public function postViewAction(Request $request, Post $post)
    {
        $comment = new Comment();
        $comment->setPost($post);
        $form = $this->createForm(CommentType::class, $comment);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();
            
            return $this->redirectToRoute('post_view', ['id' => $post->getId()]);
        }
        
        $commentDeleters = [];
        
        foreach ($post->getComments() as $comment)
        {
            $commentDeleters[$comment->getId()] =
                $this->createDeleteForm($comment)->createView();
        }
        
        return $this->render('BlogBundle:Post:view.html.twig',
            [
                'post' => $post,
                'commentDeleters' => $commentDeleters,
                'form' => $form->createView()
            ]
        );
    }
}

