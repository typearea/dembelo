<?php

namespace DembeloMain\Controller\Dashboard;

use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use DembeloMain\Model\Repository\TopicRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class DefaultController extends Controller
{
    /** @var TextNodeRepositoryInterface */
    private $topicRepository;
    private $templating;

    public function __construct(EngineInterface $templating, TopicRepositoryInterface $topicRepository)
    {
        $this->templating = $templating;
        $this->topicRepository = $topicRepository;
    }

    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function indexAction()
    {
        return $this->templating->renderResponse(
            'DembeloMain::dashboard/index.html.twig',
            array('topics' => $this->topicRepository->findByStatusActive())
        );
    }
}
