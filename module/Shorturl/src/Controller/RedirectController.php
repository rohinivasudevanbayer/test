<?php
namespace Shorturl\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Shorturl\Model\NotFound;
use Shorturl\Model\NotFoundTable;
use Shorturl\Model\ShorturlsTable;
use Shorturl\Model\ShorturlVisit;
use Shorturl\Model\ShorturlVisitsTable;

class RedirectController extends AbstractActionController
{
    protected $shorturlsTable;
    protected $shorturlVisitsTable;

    public function __construct(ShorturlsTable $shorturlTable, ShorturlVisitsTable $shorturlVisitsTable, NotFoundTable $notFoundTable)
    {
        $this->shorturlsTable = $shorturlTable;
        $this->shorturlVisitsTable = $shorturlVisitsTable;
        $this->notFoundTable = $notFoundTable;
    }

    public function indexAction()
    {
        $domain = $this->getDomain();
        $urlCode = $this->params()->fromRoute('urlcode', false);
        if ($domain && $urlCode) {
            $shorturl = $domain . '/' . $urlCode;
            $url = $this->shorturlsTable->getActiveShorturl($shorturl);
            // redirect if url has been found
            if ($url && !$url->isExpired()) {
                $url->visits++;
                $this->shorturlsTable->save($url);
                $visit = new ShorturlVisit();
                $visit->shorturl_id = $url->id;
                $visit->visit_date = date('Y-m-d H:i:s');
                $this->shorturlVisitsTable->save($visit);
                return $this->redirect()->toUrl($url->target_url);
            }
            // show error message if url has not been found
            $notFound = new NotFound();
            $notFound->short_url = $shorturl;
            $notFound->created_at = date('Y-m-d H:i:s');
            $this->notFoundTable->save($notFound);
            return new ViewModel([
                'shorturl' => $shorturl,
            ]);
        }
        // redirect to welcome page if no shorturl has been given
        return $this->redirect()->toRoute('shorturl');
    }

    private function getDomain()
    {
        $domain = false;
        // TODO: Remove
        // Workaround for go/ and go.bayer.cnb redirections
        if (!empty($_GET['intranet'])) {
          $domain = 'go.cnb';
        }
        else if (!empty($_SERVER['HTTP_HOST'])) {
            $domain = $_SERVER['HTTP_HOST'];
            // Fetch test domains
            if (strpos($domain, 'test') !== false) {
                $domain = str_replace('test.', '', $domain);
            }
        }
        return $domain;
    }
}
