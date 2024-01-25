<?php
namespace Shorturl\Controller;

use Auth\Model\UsersTable;
use Laminas\Authentication\AuthenticationService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\I18n\Translator;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Shorturl\Form\ShorturlForm;
use Shorturl\Model\Admins2DomainsTable;
use Shorturl\Model\BlacklistTable;
use Shorturl\Model\DomainsProvider;
use Shorturl\Model\Shorturl2User;
use Shorturl\Model\Shorturl;
use Shorturl\Model\ShorturlHistory;
use Shorturl\Model\Shorturls2UsersTable;
use Shorturl\Model\ShorturlsHistoryTable;
use Shorturl\Model\ShorturlsTable;
use Shorturl\Model\ShorturlVisitsTable;

class ShorturlController extends AbstractActionController
{
    private $admins2DomainsTable;
    private $shorturlsTable;
    private $shorturls2UsersTable;
    private $blacklistTable;
    private $usersTable;
    private $historyTable;
    private $visitsTable;
    private $domainsProvider;
    private $authService;
    private $user;
    private $translator;
    private $config;

    private $filters = [
        ShorturlsTable::FILTER_ALL => 'All',
        ShorturlsTable::FILTER_MY => 'Only my Urls',
        ShorturlsTable::FILTER_SHARED => 'Only Shared Urls',
    ];

    public function __construct(
        Admins2DomainsTable $admins2DomainsTable,
        ShorturlsTable $shorturlsTable,
        Shorturls2UsersTable $shorturls2UsersTable,
        BlacklistTable $blacklistTable,
        UsersTable $usersTable,
        ShorturlsHistoryTable $historyTable,
        ShorturlVisitsTable $visitsTable,
        DomainsProvider $domainsProvider,
        AuthenticationService $authService,
        Translator $translator,
        $config
    ) {
        $this->shorturlsTable = $shorturlsTable;
        $this->shorturls2UsersTable = $shorturls2UsersTable;
        $this->blacklistTable = $blacklistTable;
        $this->usersTable = $usersTable;
        $this->historyTable = $historyTable;
        $this->visitsTable = $visitsTable;
        $this->domainsProvider = $domainsProvider;
        $this->authService = $authService;
        $this->user = $this->authService->getIdentity();
        $this->translator = $translator;
        $this->config = $config;
    }

    /**
     * Display list of short urls
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        echo "shortUrlIndexAction";
        $this->layout()->showSearch = true;

        $sort = $this->params()->fromQuery('sort');
        $order = $this->params()->fromQuery('order', 'asc');

        $domain = $this->params()->fromQuery('domain', ShorturlsTable::DOMAIN_ALL);
        $domainId = $this->domainsProvider->getIdByDomain($domain);

        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;

        $q = $this->getRequest()->getQuery('q');
        $q = trim(htmlspecialchars($q));
        $q = preg_replace('/[\s,;]+/', ',', $q);
        $searchTerms = explode(',', $q);

        $filter = $this->params()->fromQuery('filter');
        if (empty($filter) || !array_key_exists($filter, $this->filters)) {
            $filter = ShorturlsTable::FILTER_MY;
        }
        $paginator = $this->shorturlsTable->getAllShortUrlsPaginated($domainId, $searchTerms, $sort, $order, $filter, $this->user->id);

        $itemsPerPage = (int) $this->config["shorturls_list"]["items_per_page"];
        $paginator->setItemCountPerPage($itemsPerPage);
        $paginator->setCurrentPageNumber($page);

        $queryParams = $this->getQueryParams();
        $searchQueryParams = $queryParams;

        $searchParams = [
            'route' => 'shorturl',
            'routeParams' => [],
            'routeQuery' => $searchQueryParams,
        ];

        if (!empty($q)) {
            $searchParams['q'] = str_replace(',', ' ', $q);
        }

        $this->layout()->searchParams = $searchParams;

        // save user names separately for shared urls to be able to display usernames in list
        $userNames = [];
        if ((int) $filter === ShorturlsTable::FILTER_SHARED) {
            foreach ($paginator as $shorturl) {
                if ($shorturl->user_id !== $this->user->id) {
                    try {
                        $user = $this->usersTable->getByIdCached($shorturl->user_id);
                        $userNames[$user->id] = $user->name . ', ' . $user->firstname;
                    } catch (\Exception $e) {
                        // ignore
                    }
                }
            }
        }

        $viewParams = [
            'paginator' => $paginator,
            'page' => $page,
            'user' => $this->user,
            'userNames' => $userNames,
            'queryParams' => $queryParams,
            'domainParam' => $domain,
            'filterParam' => $filter,
            'domains' => $this->domainsProvider->fetchAll(),
            'filters' => $this->filters,
            'reminderWeeks' => (int) $this->config["reminder1"]["weeks_until_expiration"],
        ];

        if (!empty($this->params()->fromQuery('liid'))) {
            $viewParams['liid'] = (int) $this->params()->fromQuery('liid');
        }

        if (!empty($this->params()->fromQuery('luid'))) {
            $viewParams['luid'] = (int) $this->params()->fromQuery('luid');
        }

        if (!empty($this->params()->fromQuery('deleted'))) {
            $viewParams['deleted'] = (int) $this->params()->fromQuery('deleted');
        }

        return new ViewModel($viewParams);
    }

    /**
     * Get query params from query string. Removes params which equal the default values.
     *
     * @return array
     */
    private function getQueryParams()
    {
        $queryParams = $this->getRequest()->getQuery();
        $paramsDefaultValues = [
            'filter' => ShorturlsTable::FILTER_MY,
            'domain' => ShorturlsTable::DOMAIN_ALL,
            'sort' => '',
            'order' => 'desc',
            'q' => '',
        ];
        $result = [];
        foreach ($paramsDefaultValues as $paramName => $paramDefaultValue) {
            if (!empty($queryParams[$paramName]) && $queryParams[$paramName] !== $paramDefaultValue) {
                $result[$paramName] = $queryParams[$paramName];
            }
        }
        return $result;
    }

    /**
     * Show the information for a shorturl
     *
     * @return ViewModel
     */
    public function infoAction()
    {
        $this->layout()->showSearch = false;

        $viewParams = [];

        if ($this->request->isXmlHttpRequest()) {
            $viewParams['isXmlHttpRequest'] = true;
        } else {
            $viewParams['queryParams'] = $this->getQueryParams();
            $this->layout()->breadcrumbItems = array_merge($this->layout()->breadcrumbItems, [['label' => 'Information']]);
        }

        $shorturlId = $this->params()->fromRoute('id', null);
        if (null === $shorturlId) {
            $viewParams['error'] = 'ShortUrl ID not exists';
        } else {
            $shorturl = $this->shorturlsTable->getById($shorturlId);
            $viewParams['shorturl'] = $shorturl;
            $viewParams['shorturlOwner'] = $this->usersTable->getById($shorturl->user_id);
            $viewParams['shorturlSharedUsers'] = $this->usersTable->getSharedUsersForShorturl($shorturlId);
        }

        $view = new ViewModel($viewParams);
        if (!empty($viewParams['isXmlHttpRequest'])) {
            // disable layout
            $view->setTerminal(true);
        }

        return $view;
    }

    /**
     * Show the information for a shorturl revision
     *
     * @return ViewModel
     */
    public function revisionInfoAction()
    {
        $this->layout()->showSearch = false;

        $viewParams = [];

        if ($this->request->isXmlHttpRequest()) {
            $viewParams['isXmlHttpRequest'] = true;
        } else {
            $this->layout()->breadcrumbItems = array_merge($this->layout()->breadcrumbItems, [['label' => 'Revision Information']]);
        }

        $revisionId = $this->params()->fromRoute('id', null);
        if (null === $revisionId) {
            $viewParams['error'] = 'ShortUrl ID not exists';
        } else {
            $revision = $this->historyTable->getById($revisionId);
            $viewParams['revision'] = $revision;
            try {
                $viewParams['shorturlOwner'] = $this->usersTable->getById($revision->user_id);
            } catch (\Exception $e) {
                $viewParams['shorturlOwner'] = '';
            }
            if (!empty($revision->updated_by)) {
                try {
                    $viewParams['updatedUser'] = $this->usersTable->getById($revision->updated_by);
                } catch (\Exception $e) {
                    $viewParams['updatedUser'] = '';
                }
            }
        }

        $view = new ViewModel($viewParams);
        if (!empty($viewParams['isXmlHttpRequest'])) {
            // disable layout
            $view->setTerminal(true);
        }

        return $view;
    }

    /**
     * Shows the shorturl creation form and saves its contents in the DB
     *
     * @return ViewModel
     */
    public function createAction()
    {
        $this->layout()->showSearch = false;
        $this->layout()->breadcrumbItems = array_merge($this->layout()->breadcrumbItems, [['label' => 'create']]);

        $error = '';
        $availableDomains = ['domains' => $this->domainsProvider->fetchAllWithIdsAsKeys()];
        $form = new ShorturlForm($availableDomains);
        $form->get('url_code')->setValue($this->createRandomUrlCode(5));
        $form->get('expiry_date')->setValue(date('Y-m-d', strtotime(date("Y-m-d", time()) . " + 1 year")));

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('shorturl');
            }

            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                $values['target_url'] = str_replace(' ', '%20', $values['target_url']); // fix target_urls which contain an invalid space
                $values['domain_id'] = (int) $values['domains'];
                $values['short_url'] = $this->domainsProvider->getById($values['domain_id'])->domain . '/' . trim($values['url_code']);
                $values['user_id'] = (int) $this->user->id;
                $values['qr_code_settings'] = json_encode(array(
                    'height' => '220',
                    'width' => '220',
                ));
                $values['updated_by'] = $this->user->id;
                try {
                    $blacklistResult = $this->blacklistTable->findByUrlCodeAndDomainId($values['url_code'], $values['domain_id']);
                    if (count($blacklistResult)) {
                        throw new Exception($this->translator->translate('Url is in Blacklist'));
                    }

                    $shorturl = new Shorturl();
                    $shorturl->exchangeArray($values);
                    $shorturl = $this->shorturlsTable->save($shorturl);
                    $lastInsertId = $shorturl->id;
                    if ($lastInsertId === 0) {
                        $error = $this->translator->translate('There has been an unknown error.');
                    } else {
                        return $this->redirect()->toRoute(
                            'shorturl',
                            ['action' => 'index'],
                            ['query' => ['liid' => $lastInsertId]]
                        );
                    }
                } catch (\Exception $e) {
                    if (strstr($e->getMessage(), 'uniq_shorturl')) {
                        $error = $this->translator->translate('This Short Url already exists');
                    } else {
                        $error = $e->getMessage();
                        if (empty($error)) {
                            $error = $this->translator->translate('There has been an error.');
                        }
                    }
                }
            } else {
                $errorMessages = $form->getMessages();
                $error = $this->translator->translate('There has been an error.') . ':';
                if (!is_array($errorMessages)) {
                    $error .= ' ' . print_r($errorMessages, true);
                } else {
                    $error .= '<ul>';
                    foreach ($errorMessages as $field) {
                        if (is_array($field)) {
                            foreach ($field as $message) {
                                $error .= '<li>' . $message . '</li>';
                            }
                        }
                    }
                    $error .= '</ul>';
                }

            }
        }

        return new ViewModel([
            'domains' => $this->domainsProvider->fetchAll(),
            'form' => $form,
            'error' => $error,
            'queryParams' => $this->getQueryParams(),
        ]);
    }

    /**
     * Checks if the given shorturl in the given domain (both via query params) already exists.
     * If existing it returns information about the shorturl and its owner
     *
     * Only accessible via AJAX
     */
    public function existsAction()
    {
        if (!$this->request->isXmlHttpRequest()) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $urlCode = $this->getRequest()->getQuery('urlcode');
        $domainId = $this->getRequest()->getQuery('domain');
        $shorturlId = $this->params()->fromQuery('id');

        if (!empty($shorturlId)) {
            $shorturlObj = $this->shorturlsTable->getById($shorturlId);
            if (!empty($shorturlObj) && $shorturlObj->url_code === $urlCode && $this->usersTable->hasPermission($this->user, $shorturlObj)) {
                return new JsonModel(['message' => false]);
            }
        }

        $blackListResult = $this->blacklistTable->findByUrlCodeAndDomainId($urlCode, $domainId);
        if (count($blackListResult)) {
            return new JsonModel(['message' => 'blacklist']);
        }

        $existingShorturls = $this->shorturlsTable->findByUrlCodeAndDomainId($urlCode, $domainId);
        if (count($existingShorturls)) {
            $existingShorturl = $existingShorturls->current();
            $existingShorturlOwner = $this->usersTable->getOwnerOfShorturl($existingShorturl->id);
            if (null !== $existingShorturlOwner) {
                $owner = [
                    'name' => $existingShorturlOwner->name,
                    'firstname' => $existingShorturlOwner->firstname,
                    'email' => $existingShorturlOwner->email,
                ];
            } else {
                $owner = [
                    'name' => '',
                    'firstname' => 'unknown',
                    'email' => '',
                ];
            }
            $result = [
                'short_url' => $existingShorturl->short_url,
                'target_url' => $existingShorturl->target_url,
                'owner' => $owner,
            ];
            return new JsonModel(['message' => $result]);
        }

        return new JsonModel(['message' => false]);
    }

    /**
     * Receives content of a png image via POST and sends it back to the browser as download.
     * This is used for downloading browser generated QR code images.
     *
     * File content needs to be sent in $_POST['imgdata']
     * Filename has to be in in $_POST['filename'], the resulting filename will be qrcode_<filename>.png e.g. qrcode_12345.png
     */
    public function downloadQrcodeAction()
    {
        if (!$this->getRequest()->isPost()) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $post = $this->getRequest()->getPost();
        if (!isset($post['imgdata']) || !isset($post['filename']) || isset($post['cancel'])) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        $imageData = $post['imgdata'];
        $filename = $post['filename'];

        header('Content-type: image/png');
        header('Content-Disposition: attachment; filename="qrcode_' . $filename . '.png"');

        $encoded = $imageData;
        $encoded = str_replace(' ', '+', $encoded);
        $decoded = base64_decode($encoded);

        echo $decoded;
        exit();
    }

    /**
     * Shows the shorturl edit form and saves its contents in the DB
     *
     * @return ViewModel|void
     */
    public function editAction()
    {
        $shorturlId = $this->params()->fromRoute('id', null);
        try {
            $shorturlObj = $this->shorturlsTable->getById($shorturlId);
        } catch (\Exception $e) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $this->layout()->showSearch = false;
        $this->layout()->breadcrumbItems = array_merge($this->layout()->breadcrumbItems, [['label' => 'edit']]);

        if (!$this->usersTable->hasPermission($this->user, $shorturlObj)) {
            $error = $this->translator->translate("You don't have permission");
            $showErrorOnly = true;
            return new ViewModel([
                'showErrorOnly' => true,
                'error' => $error,
                'queryParams' => $this->getQueryParams(),
            ]);
        }

        $availableDomains = ['domains' => $this->domainsProvider->fetchAllWithIdsAsKeys()];
        $form = new ShorturlForm($availableDomains);

        $reminderWeeks = (int) $this->config["reminder1"]["weeks_until_expiration"];
        $willExpireSoon = $shorturlObj->willExpireSoon($reminderWeeks);
        $isOwner = $shorturlObj->isOwnedBy($this->user->id);
        $owner = $this->usersTable->getById($shorturlObj->user_id);
        $showErrorOnly = false;
        $error = '';
        $shorturlId = $shorturlObj->id;

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            if (isset($data['cancel'])) {
                return $this->redirect()->toRoute('shorturl');
            }
            $data['url_code'] = $shorturlObj->url_code;
            $data['domains'] = $shorturlObj->domain_id;
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();

                try {
                    $shorturlHistoryEntry = new ShorturlHistory();
                    $shorturlHistoryEntry->newFromShortUrl($shorturlObj);
                    $this->historyTable->save($shorturlHistoryEntry);

                    $shorturlObj->target_url = str_replace(' ', '%20', $values['target_url']); // fix to be able to edit target_urls which contain an invalid space
                    $shorturlObj->status = (int) $values['status'];
                    // reset reminder flags if expiry_date has been changed
                    if ($values['expiry_date'] !== $shorturlObj->expiry_date) {
                        $shorturlObj->expiry_date = $values['expiry_date'];
                        $shorturlObj->sent_reminder1 = 0;
                        $shorturlObj->sent_reminder2 = 0;
                        $shorturlObj->sent_expiration_notification = 0;
                    }

                    $shorturlObj->updated_by = $this->user->id;
                    $shorturlObj->updated_at = date('Y-m-d H:i:s', time());
                    $result = $this->shorturlsTable->save($shorturlObj);

                    if (!empty($result)) {
                        return $this->redirect()->toRoute(
                            'shorturl',
                            ['action' => 'index'],
                            ['query' => ['luid' => $shorturlId, 'page' => $this->getRequest()->getQuery('page', 0)]]
                        );
                    } else {
                        $error = $this->translator->translate('There has been an unknown error.');
                    }
                } catch (Exception $e) {
                    if (strstr($e->getMessage(), 'uniq_shorturl')) {
                        $error = $this->translator->translate('This Short Url already exists');
                    } else {
                        $error = $this->translator->translate('There has been an error.');
                    }
                }
            } else {
                $error = $this->translator->translate('There has been an error.');
            }
        } else {
            $shorturlData = (array) $shorturlObj;
            $shorturlData['domains'] = $shorturlData['domain_id'];
            unset($shorturlData['domain_id']);
            $form->setData($shorturlData);
        }
        return new ViewModel([
            'domains' => $this->domainsProvider->fetchAll(),
            'form' => $form,
            'error' => $error,
            'isExpired' => $shorturlObj->isExpired(),
            'willExpireSoon' => $willExpireSoon,
            'isOwner' => $isOwner,
            'owner' => $owner,
            'isInActive' => !$shorturlObj->status,
            'shorturl' => $shorturlObj,
            'queryParams' => $this->getQueryParams(),
        ]);
    }

    /**
     * Retrieve the list of shared users for the given url
     *
     * Only accessible via AJAX
     *
     * @return JsonModel|void
     */
    public function getSharedUserListAction()
    {
        if (!$this->request->isXmlHttpRequest()) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $urlId = (int) $this->getRequest()->getQuery('url_id');

        $rows = $this->usersTable->getSharedUsersForShorturl($urlId);
        if (!$rows) {
            return new JsonModel(['success' => false, 'message' => 'db error']);
        }

        // cleanup result, only return needed data
        $shares = [];
        foreach ($rows as $user) {
            array_push($shares, [
                'id' => $user->id,
                'name' => $user->name,
                'firstname' => $user->firstname,
                'email' => $user->email,
                'self' => $this->user->id === $user->id,
            ]);
        }

        return new JsonModel(['success' => true, 'shares' => $shares]);
    }

    /**
     * Find users by given string
     *
     * Only accessible via AJAX
     *
     * @return JsonModel|void
     */
    protected function findUsersAction()
    {
        if (!$this->request->isXmlHttpRequest()) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $q = $this->getRequest()->getQuery('q');
        $q = trim(htmlspecialchars($q));
        $q = preg_replace('/[\s,;]+/', ',', $q);
        $searchTerms = explode(',', $q);

        $responseArray = array();
        if (!empty($q)) {
            $users = $this->usersTable->findUsers($searchTerms);
            if (!empty($users)) {
                foreach ($users as $user) {
                    $responseArray[] = [
                        'label' => ucfirst($user->name) . ', ' . ucfirst($user->firstname) . ' (' . $user->email . ')',
                        'value' => $user->id,
                    ];
                }
            }
        }
        return new JsonModel(['success' => true, 'shares' => $responseArray]);
    }

    /**
     * Add a share by given user id and url id
     *
     * Only accessible via AJAX
     *
     * @return JsonModel|void
     */
    protected function addShareAction()
    {
        if (!$this->request->isXmlHttpRequest()) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $userId = (int) $this->getRequest()->getPost('user_id');
        $urlId = (int) $this->getRequest()->getPost('url_id');

        if (empty($userId) || empty($urlId)) {
            $msg = $this->translator->translate('Empty Parameter');
            $this->getResponse()->setStatusCode(400)->setReasonPhrase($msg);
            return new JsonModel(['message' => $msg]);
        }

        $user = $this->usersTable->getById($userId);
        if (!empty($user)) {
            $shortUrl = $this->shorturlsTable->getById($urlId);

            if ($this->usersTable->hasPermission($this->user, $shortUrl)) {
                if ($this->shorturls2UsersTable->isShare($urlId, $userId)) {
                    $msg = $this->translator->translate('url already shared');
                    $this->getResponse()->setStatusCode(409)->setReasonPhrase($msg);
                    return new JsonModel(['message' => $msg]);
                }

                $share = new Shorturl2User();
                $share->exchangeArray([
                    'user_id' => $userId,
                    'shorturl_id' => $urlId,
                ]);
                $share = $this->shorturls2UsersTable->save($share);
                $lastInsertId = $share->id;

                if (!empty($lastInsertId)) {
                    return new JsonModel(['message' => $lastInsertId]);
                } else {
                    $msg = $this->translator->translate('share could not be added');
                    $this->getResponse()->setStatusCode(500)->setReasonPhrase($msg);
                    return new JsonModel(['message' => $msg]);
                }
            } else {
                $msg = $this->translator->translate('no permissions');
                $this->getResponse()->setStatusCode(403)->setReasonPhrase($msg);
                return new JsonModel(['message' => $msg]);
            }
        }
        $msg = $this->translator->translate('user not using shorturl');
        $this->getResponse()->setStatusCode(400)->setReasonPhrase($msg);
        return new JsonModel(['message' => $msg]);
    }

    /**
     * Remove a share by given user id and url id
     *
     * Only accessible via AJAX
     *
     * @return JsonModel|void
     */
    public function removeShareAction()
    {
        if (!$this->request->isXmlHttpRequest()) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $userId = (int) $this->getRequest()->getQuery('user_id');
        $urlId = (int) $this->getRequest()->getQuery('url_id');

        $shortUrl = $this->shorturlsTable->getById($urlId);
        if ($this->usersTable->hasPermission($this->user, $shortUrl)) {
            $res = $this->shorturls2UsersTable->removeShare($urlId, $userId);
            return new JsonModel(['message' => $res ? 1 : 0]);
        } else {
            $msg = $this->translator->translate('no permissions');
            $this->getResponse()->setStatusCode(403)->setReasonPhrase($msg);
            return new JsonModel(['message' => $msg]);
        }
    }

    /**
     * Change the owner of a shorturl
     *
     * Only accessible via AJAX
     *
     * @return JsonModel|void
     */
    protected function changeOwnerAction()
    {
        if (!$this->request->isXmlHttpRequest()) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $userId = (int) $this->getRequest()->getPost('user_id');
        $urlId = (int) $this->getRequest()->getPost('url_id');

        if (empty($userId) || empty($urlId)) {
            $msg = $this->translator->translate('Empty Parameter');
            $this->getResponse()->setStatusCode(400)->setReasonPhrase($msg);
            return new JsonModel(['message' => $msg]);
        }

        try {
            $shortUrl = $this->shorturlsTable->getById($urlId);
            if ($this->usersTable->hasPermission($this->user, $shortUrl)) {
                if ($userId === $shortUrl->user_id) {
                    $msg = $this->translator->translate('This ShortUrl is already owned by that user');
                    $this->getResponse()->setStatusCode(400)->setReasonPhrase($msg);
                    return new JsonModel(['message' => $msg]);
                }
                $this->shorturls2UsersTable->removeShare($urlId, $userId);
                $shortUrl->user_id = $userId;
                $this->shorturlsTable->save($shortUrl);

                return new JsonModel(['message' => 'OK']);
            } else {
                $msg = $this->translator->translate('no permissions');
                $this->getResponse()->setStatusCode(403)->setReasonPhrase($msg);
                return new JsonModel(['message' => $msg]);
            }
        } catch (Exeption $e) {
            $msg = "Internal Server Error";
            $this->getResponse()->setStatusCode(500)->setReasonPhrase($msg);
            return new JsonModel(['message' => $msg]);
        }
        $msg = $this->translator->translate('user not using shorturl');
        $this->getResponse()->setStatusCode(400)->setReasonPhrase($msg);
        return new JsonModel(['message' => $msg]);
    }

    /**
     * Deletes a shorturl after asking if the shorturl really should be deleted
     *
     * @return ViewModel
     */
    protected function deleteAction(): ViewModel
    {
        $shorturlId = $this->params()->fromRoute('id', null);
        try {
            $shorturlObj = $this->shorturlsTable->getById($shorturlId);
        } catch (\Exception $e) {
            return new ViewModel([
                'error' => $this->translator->translate("ShortUrl ID not exists"),
            ]);
        }
        if (empty($shorturlId)) {
            return new ViewModel([
                'error' => $this->translator->translate("ShortUrl ID not exists"),
            ]);
        } else {
            if (!$this->usersTable->hasPermission($this->user, $shorturlObj)) {
                return new ViewModel([
                    'error' => $this->translator->translate("You don't have permission"),
                    'queryParams' => $this->getQueryParams(),
                ]);
            } else {
                if ($this->getRequest()->isPost()) {
                    $post = $this->getRequest()->getPost();
                    if (isset($post['delete']) === true && $this->shorturlsTable->delete($shorturlObj)) {
                        // delete related db entries
                        $this->shorturls2UsersTable->deleteByShorturlId($shorturlId);
                        $this->historyTable->deleteByShorturlId($shorturlId);
                        $this->visitsTable->deleteByShorturlId($shorturlId);
                        $this->redirect()->toRoute('shorturl', ['action' => 'index'], ['query' => array_merge($this->getQueryParams(), ['deleted' => 1])]);
                    } else {
                        $this->redirect()->toRoute('shorturl', ['action' => 'index'], ['query' => array_merge($this->getQueryParams())]);
                    }
                }
            }
            return new ViewModel([
                'shorturl' => $shorturlObj,
                'queryParams' => $this->getQueryParams(),
            ]);
        }
    }

    /**
     * Show the information for a shorturl
     *
     * @return ViewModel
     */
    protected function stateAction()
    {
        $this->layout()->showSearch = false;

        $shorturlId = $this->params()->fromRoute('id', null);
        $newState = $this->getRequest()->getQuery('new_state', null);
        $viewParams = [];

        if ($this->request->isXmlHttpRequest()) {
            $viewParams['isXmlHttpRequest'] = true;
        } else {
            $this->layout()->breadcrumbItems = array_merge($this->layout()->breadcrumbItems, [['label' => 'title for status modal']]);
            $viewParams['queryParams'] = $this->getQueryParams();
        }

        if (null === $shorturlId) {
            $viewParams['error'] = 'ShortUrl ID not exists';
        } else {
            $shorturlObj = $this->shorturlsTable->getById($shorturlId);
            if (!$this->usersTable->hasPermission($this->user, $shorturlObj)) {
                $viewParams['error'] = "You don't have permission";
                $viewParams['queryParams'] = $this->getQueryParams();
            } else {
                if (null !== $newState) { // change state
                    $shorturlObj = $this->shorturlsTable->getById($shorturlId);

                    $shorturlHistoryEntry = new ShorturlHistory();
                    $shorturlHistoryEntry->newFromShortUrl($shorturlObj);
                    $this->historyTable->save($shorturlHistoryEntry);

                    $shorturlObj->status = $newState;
                    $shorturlObj->updated_by = $this->user->id;
                    $shorturlObj->updated_at = date('Y-m-d H:i:s', time());
                    $this->shorturlsTable->save($shorturlObj);
                    $this->redirect()->toRoute('shorturl', [], ['query' => array_merge($this->getQueryParams(), ['luid' => $shorturlObj->id])]);
                    return;
                } else { // show confirmation request message (for both ajax and direct requests)
                    $viewParams['shorturl'] = $shorturlObj;
                    $viewParams['queryParams'] = $this->getQueryParams();
                }
            }
        }

        $view = new ViewModel($viewParams);
        if (!empty($viewParams['isXmlHttpRequest'])) {
            // disable layout
            $view->setTerminal(true);
        }

        return $view;
    }

    /**
     * Display the QrCode page for downloading and changing the qr code settings
     *
     * @return ViewModel
     */
    protected function qrcodeAction(): ViewModel
    {
        $this->layout()->showSearch = false;
        $this->layout()->breadcrumbItems = array_merge($this->layout()->breadcrumbItems, [['label' => 'Create QR Code']]);

        $shorturlId = $this->params()->fromRoute('id', null);

        $viewParams = [
            'queryParams' => $this->getQueryParams(),
        ];
        if (empty($shorturlId)) {
            $viewParams['error'] = 'ShortUrl ID not exists';
        } else {
            $shorturlObj = $this->shorturlsTable->getById($shorturlId);
            if (!$this->usersTable->hasPermission($this->user, $shorturlObj)) {
                $viewParams['error'] = "You don't have permission";
                $viewParams['no_permission'] = true;
                $viewParams['queryParams'] = $this->getQueryParams();
            } else {
                $viewParams['shorturl'] = $shorturlObj;
            }
        }
        return new ViewModel($viewParams);
    }

    /**
     * Updates the QR code settings for a shorturl
     *
     * Only accessible via AJAX
     *
     * @return JsonModel|void
     */
    protected function updateQrcodeAction()
    {
        if (!$this->request->isXmlHttpRequest()) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if (empty($post['id'])) {
                return new JsonModel(['message' => false]);
            }
            $shorturlId = $post['id'];
            $shorturlObj = $this->shorturlsTable->getById($shorturlId);
            if (!$this->usersTable->hasPermission($this->user, $shorturlObj)) {
                return new JsonModel(['message' => false]);
            }

            $shorturlHistoryEntry = new ShorturlHistory();
            $shorturlHistoryEntry->newFromShortUrl($shorturlObj);
            $this->historyTable->save($shorturlHistoryEntry);

            $shorturlObj->qr_code_settings = json_encode(['height' => $post['height'], 'width' => $post['width']]);
            $shorturlObj->updated_by = $this->user->id;
            $shorturlObj->updated_at = date('Y-m-d H:i:s', time());
            $result = $this->shorturlsTable->save($shorturlObj);
            return new JsonModel(['message' => (boolean) $result]);
        }
        return new JsonModel(['message' => false]);
    }

    /**
     * Action to show a paginated list of revisions for a given shorturl
     *
     * @return ViewModel
     */
    protected function revisionsAction(): ViewModel
    {
        $this->layout()->showSearch = false;
        $this->layout()->breadcrumbItems = array_merge($this->layout()->breadcrumbItems, [['label' => 'Revision']]);

        $shorturlId = $this->params()->fromRoute('id', null);
        $viewParams = [
            'id' => $shorturlId,
            'queryParams' => $this->getQueryParams(),
        ];

        if (empty($shorturlId)) {
            $viewParams['error'] = 'ShortUrl ID not exists';
            return new ViewModel($viewParams);
        }

        try {
            $shorturlObj = $this->shorturlsTable->getById($shorturlId);
        } catch (\Exception $e) {
            $viewParams['error'] = 'ShortUrl ID not exists';
            return new ViewModel($viewParams);
        }

        if (!$this->usersTable->hasPermission($this->user, $shorturlObj)) {
            $viewParams['error'] = "You don't have permission";
            $viewParams['queryParams'] = $this->getQueryParams();
            $viewParams['showErrorOnly'] = true;
            return new ViewModel($viewParams);
        }

        $viewParams['shorturl'] = $shorturlObj;

        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        $viewParams['page'] = $page;

        // save user objects separately to be able to print names instead of ids (DB resultset can not be changed)
        $userNames = [];
        try {
            $user = $this->usersTable->getByIdCached($shorturlObj->updated_by);
            $userNames[$shorturlObj->updated_by] = $user->firstname . ' ' . $user->name;
        } catch (\Exception $e) {
            // ignore
        }

        $paginator = $this->historyTable->getByShorturlIdPaginated($shorturlId);
        $paginator->setCurrentPageNumber($page);
        $itemsPerPage = (int) $this->config["history"]["items_per_page"];
        $paginator->setItemCountPerPage($itemsPerPage);

        $viewParams['paginator'] = $paginator;

        if (!empty($paginator)) {
            $viewParams['rowsCount'] = $paginator->getTotalItemCount();

            $shorturlRows = $paginator->getCurrentItems();
            foreach ($shorturlRows as $row) {
                if (!isset($userNames[$row->updated_by])) {
                    try {
                        $user = $this->usersTable->getByIdCached($row->updated_by);
                        $userNames[$row->updated_by] = $user->firstname . ' ' . $user->name;
                    } catch (\Exception $e) {
                        // ignore
                    }
                }
            }
        }
        $viewParams['userNames'] = $userNames;

        if (1 === (int) $this->params()->fromQuery('restored')) {
            $viewParams['restored'] = true;
        }

        return new ViewModel($viewParams);
    }

    /**
     * Shows a confirmation message and restores a given shorturl
     *
     * @return ViewModel
     */
    protected function restoreRevisionAction(): ViewModel
    {
        $revisionId = $this->params()->fromRoute('id');
        $page = $this->getRequest()->getQuery('page');

        $viewParams = [];
        $viewParams['page'] = $page;

        try {
            $revision = $this->historyTable->getById($revisionId);
        } catch (\Exception $e) {
            $viewParams['error'] = 'ShortUrl ID not exists';
            return new ViewModel($viewParams);
        }
        $shorturlId = $revision->shorturl_id;

        try {
            $shorturlObj = $this->shorturlsTable->getById($shorturlId);
        } catch (\Exception $e) {
            $viewParams['error'] = 'ShortUrl ID not exists';
            return new ViewModel($viewParams);
        }

        try {
            // save current shorturl data as new history entry
            $shorturlHistoryEntry = new ShorturlHistory();
            $shorturlHistoryEntry->newFromShortUrl($shorturlObj);
            $this->historyTable->save($shorturlHistoryEntry);

            // save old revision data in shorturl
            $revisionData = get_object_vars($revision);
            $revisionData['id'] = $revisionData['shorturl_id'];
            $revisionData['updated_at'] = date('Y-m-d H:i:s', time());
            $revisionData['updated_by'] = $this->user->id;
            $newShorturlObj = new Shorturl();
            $newShorturlObj->exchangeArray($revisionData);
            $result = $this->shorturlsTable->save($newShorturlObj);
            if (!empty($result)) {
                $this->redirect()->toRoute('shorturl', ['action' => 'revisions', 'id' => $shorturlId], ['query' => ['page' => $page, 'restored' => 1]]);
            } else {
                $viewParams['error'] = 'There has been an unknown error.';
            }
        } catch (\Exception $e) {
            if (strstr($e->getMessage(), 'uniq_shorturl')) {
                $viewParams['error'] = 'This Short Url already exists';
            } else {
                $viewParams['error'] = 'There has been an error.';
            }
        }
        return new ViewModel($viewParams);
    }

    /**
     * Display the statistics for the given shorturl
     *
     * @return ViewModel
     */
    protected function statisticsAction()
    {
        $this->layout()->showSearch = false;
        $this->layout()->breadcrumbItems = array_merge($this->layout()->breadcrumbItems, [['label' => 'Statistic']]);

        $shorturlIds = $shorturlIdsParam = $this->params()->fromQuery('ids', $this->params()->fromRoute('ids', null));
        if (empty($shorturlIds)) {
            return new ViewModel(['error' => 'ShortUrl ID not exists']);
        }

        $viewParams = [
            'queryParams' => $this->getQueryParams(),
        ];

        $startDate = $this->params()->fromQuery('start');
        $startDate = empty($startDate) ? date('Y-m-d', strtotime('-1 month')) : $startDate;
        $viewParams['start'] = $startDate;
        $endDate = $this->params()->fromQuery('end');
        $endDate = empty($endDate) ? date('Y-m-d', time()) : $endDate;
        $viewParams['end'] = $endDate;
        $endDate = date('Y-m-d', strtotime($endDate));

        $timeDifference = round((strtotime($endDate) - strtotime($startDate)) / 86400);
        $rangeDays = $timeDifference >= 0 && $timeDifference <= 30;
        $rangeWeeks = $timeDifference >= 31 && $timeDifference <= 180;

        $shorturlIds = strstr($shorturlIds, ',') ? explode(",", $shorturlIds) : array($shorturlIds);
        $shorturlIds = array_unique($shorturlIds);

        // remove shorturls with missing access permission
        $shorturlIds = array_filter($shorturlIds, function ($shorturlId) {
            try {
                $shorturlObj = $this->shorturlsTable->getById($shorturlId);
                return $this->usersTable->hasPermission($this->user, $shorturlObj);
            } catch (\Exception $e) {
                return false;
            }
        });

        if ($rangeDays == true) {
            $data = $this->visitsTable->getDayStatistic($shorturlIds, $startDate, $endDate);
            $dataRange = 'days';
        } elseif ($rangeWeeks == true) {
            $data = $this->visitsTable->getWeekStatistic($shorturlIds, $startDate, $endDate);
            $dataRange = 'weeks';
        } else {
            $data = $this->visitsTable->getMonthStatistic($shorturlIds, $startDate, $endDate);
            $dataRange = 'months';
        }

        if (empty($data)) {
            return new ViewModel(['error' => 'There is no data available']);
        }

        $viewParams['ids'] = implode(",", $shorturlIds);
        $viewParams['data'] = $data['jsonData'];
        $viewParams['shorturls'] = $this->shorturlsTable->getByIds($shorturlIds);
        $viewParams['shorturls']->buffer();
        $viewParams['start'] = $startDate;
        $viewParams['end'] = $endDate;
        $viewParams['dataRange'] = $dataRange;

        return new ViewModel($viewParams);
    }

    /**
     * Autocomplete for statistics shorturl search field
     *
     * Only accessible via AJAX
     *
     * @return JsonModel
     */
    protected function urlListAction()
    {
        if (!$this->request->isXmlHttpRequest()) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $q = $this->params()->fromQuery('q');
        if (!empty($q)) {
            $rows = $this->shorturlsTable->findAutocompleteShorturl($q, $this->user);
            $responseArray = array();
            if (!empty($rows)) {
                foreach ($rows as $k => $row) {
                    $responseArray[$k]['label'] = $row->short_url;
                    $responseArray[$k]['value'] = $row->id;
                }
            }
            return new JsonModel($responseArray);
        }
        return new JsonModel(['message' => false]);
    }

    /**
     * Create a random string as default value for the shorturl creation form
     *
     * @param integer $length The length of the string to generate
     * @return string
     */
    private function createRandomUrlCode(int $length = 5): string
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyz1234567890';
        $alphabetLength = strlen($alphabet);
        $urlCode = '';
        for ($i = 0; $i < $length; $i++) {
            $randomPos = rand(0, $alphabetLength - 1);
            $char = $alphabet[$randomPos];
            $urlCode .= $char;
        }
        return $urlCode;
    }
}
