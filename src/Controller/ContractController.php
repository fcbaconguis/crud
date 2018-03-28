<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Contract;
use App\Entity\Page;
use App\Form\ContractType;
use App\Repository\UserRepository;
use App\Repository\ContractRepository;
use App\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Spatie\PdfToImage\Pdf;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

/**
 * @Route("/contracts")
 */
class ContractController extends Controller
{


    /**
     * @var string 
     */
    private $imageDestination;

    public function __construct(string $imageDestination)
    {
        $this->imageDestination = $imageDestination;
    }

    /**
     * @Route("/{id}", name="contract_index", methods="GET")
     */
    public function index(ContractRepository $contractRepository,$id): Response
    {
        return $this->render('contract/index.html.twig', ['contracts' => $contractRepository->findByUserId($id)]);
    }

    /**
     * @Route("/{id}/new", name="contract_new", methods="GET|POST")
     */
    public function new(Request $request, $id): Response
    {
        $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->find($id);

        if (!$user) {
            $this->addFlash(
                'error',
                'No user found!'
            );
            return $this->redirectToRoute('user_index');
        }

        $contract = new Contract();
        $form = $this->createForm(ContractType::class, $contract);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contract->setUser($user);
            $em = $this->getDoctrine()->getManager();
            $em->persist($contract);

            // Get Web Path
            $webPath        = $this->get('kernel')->getRootDir() . '/../public';

            // Load Vich Uploader Helper
            $helper         = $this->get("vich_uploader.templating.helper.uploader_helper");

            // Generate the uploaded file
            $contractFile   = $webPath.$helper->asset($contract, 'file');

            // Process the uploaded PDF File
            $pdf            = new Pdf($contractFile);

            // Get Number of pages of the contract
            $pages          = $pdf->getNumberOfPages();

            // Set where to save the converted pdf
            $folderToSave   = $this->imageDestination;

            $pathToWhereImageShouldBeStored = $webPath.$folderToSave;

            $fileParts      = pathinfo($contractFile);

            if(!is_dir($pathToWhereImageShouldBeStored)) {
                $fileSystem = new Filesystem();

                try {
                    $fileSystem->mkdir($pathToWhereImageShouldBeStored, 0777);
                } catch (IOExceptionInterface $exception) {
                    echo "An error occurred while creating your directory at ".$exception->getPath();
                }
            }

            $files = array();
            for($i = 1; $i <= $pages; $i++){

                $fileName           = $fileParts["filename"]."-page-".$i.".jpg";

                $fileWithFullPath   = $pathToWhereImageShouldBeStored.$fileName;

                if (!file_exists($fileWithFullPath)) {
                    $pdf->setPage($i)
                        ->saveImage($fileWithFullPath);

                    $page = new Page();
                    $page->setContract($contract);
                    $page->setName($fileName);
                    $page->setNum($i);
                    $page->setDateUpdated(new \DateTimeImmutable());
                    $em->persist($page);

                }
                
            }


            $em->flush();
            $this->addFlash(
                'info',
                'Contract Successfully Saved!'
            );
            return $this->redirectToRoute('contract_index',['id' => $user->getId()]);
        }

        return $this->render('contract/new.html.twig', [
            'contract' => $contract,
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{user_id}/{id}", name="contract_show", methods="GET")
     */
    public function show($user_id, $id): Response
    {
        $contract = $this->getDoctrine()
                ->getRepository(Contract::class)
                ->find($id);

        if (!$contract) {
            $this->addFlash(
                'error',
                'No contract found!'
            );
            return $this->redirectToRoute('contract_index',['id' => $user_id]);
        }

        return $this->render('contract/show.html.twig', ['contract' => $contract]);
    }

    /**
     * @Route("/{user_id}/{id}/edit", name="contract_edit", methods="GET|POST")
     */
    public function edit(Request $request,$user_id, Contract $contract): Response
    {
        $form = $this->createForm(ContractType::class, $contract);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('contract_edit', ['id' => $contract->getId()]);
        }

        return $this->render('contract/edit.html.twig', [
            'contract' => $contract,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="contract_delete", methods="DELETE")
     */
    public function delete(Request $request, Contract $contract): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contract->getId(), $request->request->get('_token'))) {

            $pages              = $contract->getPages();
            $webPath            = $this->get('kernel')->getRootDir() . '/../public';
            $imageDestination   = $this->imageDestination;

            foreach($pages as $page) {

                $file = $webPath.$imageDestination.$page->getName();

                if(file_exists($file )) {
                    unlink($file);
                }

            }

            $em = $this->getDoctrine()->getManager();
            $em->remove($contract);
            $em->flush();
        }

        return $this->redirectToRoute('contract_index',['id' => $contract->getUser()->getId()]);
    }

    /**
     * @Route("/{user_id}/{id}/sign/page/{page}", name="contract_sign", methods="GET")
     */
    public function sign(Request $request, $user_id, $id, $page): Response
    {

        $contract = $this->getDoctrine()
                ->getRepository(Contract::class)
                ->find($id);

        if (!$contract) {
            $this->addFlash(
                'error',
                'No contract found!'
            );
            return $this->redirectToRoute('contract_index',['id' => $user_id]);
        }

        if(!$page) {
            $page = 1; // set default to 1 if not set
        }

        $pages = $contract->getPages();

        if(isset($pages[($page-1)])){
            $page = $pages[($page-1)];

            $path = $this->imageDestination.$page->getName();

            $filePath = $request->getUriForPath($path);
            
            $data["filePath"] = $filePath;
            $data["numPages"] = count($pages);

            return $this->render('contract/sign.html.twig', ['data' => $data]);
        }
        else
        {
            $this->addFlash(
                'error',
                'Page is not existing!'
            );
            return $this->redirectToRoute('contract_index',['id' => $contract->getUser()->getId()]);
        }       

    }
}
