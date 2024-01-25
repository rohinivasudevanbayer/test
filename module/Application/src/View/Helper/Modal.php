<?php
namespace Application\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class Modal extends AbstractHelper
{
    public function render(string $name, string $headline, string $content, string $yesButtonLabel = "Yes", string $noButtonLabel = "No", string $redirectUrl = "", string $onClickEvent = null): string
    {
        $result = $this->renderStart($name);
        $result .= $this->renderHeader($name, $headline);
        $result .= $this->renderBody($content);
        $result .= $this->renderFooter($name, $noButtonLabel, $yesButtonLabel, $redirectUrl, $onClickEvent);
        $result .= $this->renderEnd();
        return $result;
    }

    public function renderXmlHttpTop(string $name, string $headline): string
    {
        $result = '';
        $result .= $this->renderHeader($name, $headline);
        $result .= $this->renderBodyStart();
        return $result;
    }

    public function renderTop(string $name, string $headline): string
    {
        $result = '';
        $result .= $this->renderStart($name);
        $result .= $this->renderHeader($name, $headline);
        $result .= $this->renderBodyStart();
        return $result;
    }

    public function renderStart(string $name): string
    {
        return $this->getView()->partial('partial/modal_start', [
            'name' => $name,
        ]);
    }

    public function renderHeader(string $name, string $headline): string
    {
        return $this->getView()->partial('partial/modal_header', [
            'name' => $name,
            'headline' => $headline,
        ]);
    }

    public function renderBodyStart(): string
    {
        return $this->getView()->partial('partial/modal_body_start');
    }

    public function renderBody(string $content): string
    {
        $result = '';
        $result .= $this->renderBodyStart();
        $result .= $content;
        $result .= $this->renderBodyEnd();
        return $result;
    }

    public function renderBodyEnd(): string
    {
        return $this->getView()->partial('partial/modal_body_end');
    }

    public function renderFooter(string $modalName, string $noButtonLabel, string $yesButtonLabel = null, string $redirectUrl = null, string $onClickEvent = null): string
    {
        if (empty($onClickEvent) && !empty($redirectUrl)) {
            $onClickEvent = "window.location.href = '" . $redirectUrl . "'";
        }

        return $this->getView()->partial('partial/modal_footer', [
            'modalName' => $modalName,
            'noButtonLabel' => $noButtonLabel,
            'yesButtonLabel' => $yesButtonLabel,
            'redirectUrl' => $redirectUrl,
            'onClickEvent' => $onClickEvent,
        ]);
    }

    public function renderEnd(): string
    {
        return $this->getView()->partial('partial/modal_end');
    }

    public function renderBottom(string $modalName, string $noButtonLabel, string $yesButtonLabel = null, string $redirectUrl = null, string $onClickEvent = null): string
    {
        $result = '';
        $result .= $this->renderBodyEnd();
        $result .= $this->renderFooter($modalName, $noButtonLabel, $yesButtonLabel, $redirectUrl, $onClickEvent);
        $result .= $this->renderEnd();
        return $result;
    }

    public function renderXmlHttpBottom(string $modalName, string $noButtonLabel, string $yesButtonLabel = null, string $redirectUrl = null, string $onClickEvent = null): string
    {
        $result = '';
        $result .= $this->renderBodyEnd();
        $result .= $this->renderFooter($modalName, $noButtonLabel, $yesButtonLabel, $redirectUrl, $onClickEvent);
        return $result;
    }
}
