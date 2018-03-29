<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Contract;
use App\Entity\Page;
use App\Form\ContractType;
use App\Repository\UserRepository;
use App\Repository\ContractRepository;
use App\Repository\PageRepository;
use App\Service\ImageConverter;
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

            // Load Vich Uploader Helper
            $helper         = $this->get("vich_uploader.templating.helper.uploader_helper");

            $publicPath    = $this->get('kernel')->getRootDir() . '/../public';

            // Generate the uploaded file
            $contractFile   = $publicPath .$helper->asset($contract, 'file');

            // Process the uploaded PDF File
            $pdf            = new Pdf($contractFile);

            // Get Number of pages of the contract
            $pages          = $pdf->getNumberOfPages();

            // Set where to save the converted pdf
            $folderToSave   = $this->imageDestination;

            $pathToWhereImageShouldBeStored = $publicPath.$folderToSave;

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
    public function show($user_id, $id, ImageConverter $imageConverter): Response
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

        $pages = $contract->getPages();
        $publicPath = $this->get('kernel')->getRootDir() . '/../public';

        $contractTmp        = explode(".",$contract->getName());
        $contractNameFinal  = $contractTmp[0]."-final.pdf";
        $contractFinal      = $publicPath.$this->imageDestination.$contractNameFinal;
        $images = array();
        foreach($pages as $page) {
            $image = "";
            if($page->getSignedPage() != "") {
                $image = $page->getSignedPage();
            }else{
                $image = $page->getName();
            }
            $images[] =  $publicPath.$this->imageDestination.$image;
        }

        if(count($images) > 0){

            $imageConverter->images_to_pdf($images,$contractFinal);
               
            $contract->setNameFinal($contractNameFinal);
            $em = $this->getDoctrine()->getManager();
            $em->persist($contract);
            $em->flush();
        }

        return $this->render('contract/show.html.twig', [
            'contract'      => $contract,
            'contractFinal' => $this->imageDestination.$contractNameFinal
        ]);
    }

    /**
     * @Route("/{id}/delete", name="contract_delete", methods="DELETE")
     */
    public function delete(Request $request, Contract $contract): Response
    {
        $user_id = 0;
        if ($this->isCsrfTokenValid('delete'.$contract->getId(), $request->request->get('_token'))) {

            $pages              = $contract->getPages();

            foreach($pages as $page) {

                $file = $this->get('kernel')->getRootDir() . '/../public'.$this->imageDestination.$page->getName();
                $file2 = $this->get('kernel')->getRootDir() . '/../public'.$this->imageDestination.$page->getSignedPage();

                if(file_exists($file)) {
                    unlink($file);
                }
                if(file_exists($file2)) {
                    unlink($file2);
                }

            }
        
            $user_id = $contract->getUser()->getId();
            $em = $this->getDoctrine()->getManager();
            $em->remove($contract);
            $em->flush();
        }

        return $this->redirectToRoute('contract_index',['id' => $user_id]);
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
            $cpage = $pages[($page-1)];

            $path = $this->imageDestination.$cpage->getName();

            $filePath = $request->getUriForPath($path);
            
            $data["filePath"]   = $filePath;
            $data["totPage"]    = count($pages);
            $data["page"]       = $page;
            $data["user_id"]    = $user_id;
            $data["contract_id"]= $id;

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

    /**
     * @Route("/save-signed-image",name="save_signed_image",methods="POST")
     */
    public function saveSignedImage(Request $request, PageRepository $pageRepository, ImageConverter $imageConverter): Response
    {
        $imageBase64 = $request->request->get("image_base_64");
        $contractId  = $request->request->get("contract_id");
        $pageNum     = $request->request->get("page_num");

        $page = $pageRepository->findPageByPageNum($contractId,$pageNum);

        $status = "false";
        if($page) {
            $status = "true";

            $file = $page->getName();

            $fileParts  = explode(".",$file);

            $fileName   = $fileParts[0]."-final.jpg";

            $filePath   = $this->get('kernel')->getRootDir() . '/../public'.$this->imageDestination.$fileName;

            $filePath = $imageConverter->base64_to_jpeg($imageBase64,$filePath);

            $page->setSignedPage($fileName);

            $em = $this->getDoctrine()->getManager();
            $em->persist($page);
            $em->flush();
        }
        
        return $this->json(array('status' => $status));
    }
}
