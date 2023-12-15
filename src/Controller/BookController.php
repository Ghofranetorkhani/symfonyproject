<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    #[Route('/book', name: 'app_book')]
    public function index(): Response
    {
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
        ]);
    }

    #[Route('/addBook', name: 'add_book')]
    public function addBook(Request  $request,ManagerRegistry  $managerRegistry)
    {
        $book = new Book();
        $form= $this->createForm(BookType::class,$book);
        $form->handleRequest($request);
        $book->setPublished(true);
        if($form->isSubmitted()){
            $em= $managerRegistry->getManager();
            $nbBooks= $book->getAuthor()->getNbBooks();
            $book->getAuthor()->setNbBooks($nbBooks+1);
            $em->persist($book);
            $em->flush();
            //var_dump($nbBooks).die();
            return  new Response("Done!");
        }
        return $this->renderForm("book/add.html.twig",
        array('formulaireBook'=>$form));
    }
    #[Route('/listBook', name: 'list_book')]
    public function listBook(BookRepository  $repository)
    {
        return $this->render("book/list.html.twig",
            array('tabBooks'=>$repository->findAll())
        );
    }
    #[Route('/listBookA/{id}', name: 'list_bookA')]
        public function showBooks(BookRepository  $repository,$id)
    {
        $books=$repository->findbookbyauthor($id);
        return $this->render("book/list2.html.twig",
            array('tabBooks2'=>$books));
    }

    



    #[Route('/editbook/{ref}', name: 'edit_Book')]
    public function edit(BookRepository $repository, $ref, Request $request)
    {
        $author = $repository->find($ref);
        $form = $this->createForm(BookType::class, $author);
        $form->add('Edit', SubmitType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush(); // Correction : Utilisez la méthode flush() sur l'EntityManager pour enregistrer les modifications en base de données.
            return $this->redirectToRoute("list_book");
        }

        return $this->render('book/edit.html.twig', [
            'f' => $form->createView(),
        ]);
    }

    #[Route('/deletebook/{ref}', name: 'delete_Book')]
    public function delete($ref, BookRepository $repository)
    {
        $book = $repository->find($ref);


        $em = $this->getDoctrine()->getManager();
        $em->remove($book);
        $em->flush();


        return $this->redirectToRoute('list_book');
    }

}
