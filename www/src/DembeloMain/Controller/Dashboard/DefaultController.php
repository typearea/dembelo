<?php

namespace DembeloMain\Controller\Dashboard;

use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class DefaultController extends Controller
{
    /** @var TextNodeRepositoryInterface */
    private $textNodeRepository;
    private $templating;

    public function __construct(EngineInterface $templating, TextNodeRepositoryInterface $textNodeRepository)
    {
        $this->templating = $templating;
        $this->textNodeRepository = $textNodeRepository;
    }

    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function indexAction()
    {
        $textNodes = $this->textNodeRepository->findAll();
        return $this->templating->renderResponse(
            'DembeloMain::dashboard/index.html.twig',
            array('textNodes' => $textNodes)
        );
    }
}
