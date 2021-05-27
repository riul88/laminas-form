<?php

namespace Laminas\Form\View\Helper;

use Laminas\Form\ElementInterface;
use Laminas\Form\Exception;

use function array_merge;
use function array_walk_recursive;
use function implode;
use function sprintf;

class FormElementErrors extends AbstractHelper
{
    /** @var string Templates for the open/close/separators for message tags */
    protected $messageOpenFormat = '<ul%s><li>';
    /** @var string Templates for the open/close/separators for message tags */
    protected $messageCloseString = '</li></ul>';
    /** @var string Templates for the open/close/separators for message tags */
    protected $messageSeparatorString = '</li><li>';

    /** @var array Default attributes for the open format tag */
    protected $attributes = [];

    /** @var bool Whether or not to translate error messages during render. */
    protected $translateErrorMessages = true;

    /**
     * Invoke helper as functor
     *
     * Proxies to {@link render()} if an element is passed.
     *
     * @param  array            $attributes
     * @return string|FormElementErrors
     */
    public function __invoke(?ElementInterface $element = null, array $attributes = [])
    {
        if (! $element) {
            return $this;
        }

        return $this->render($element, $attributes);
    }

    /**
     * Render validation errors for the provided $element
     *
     * If {@link $translateErrorMessages} is true, and a translator is
     * composed, messages retrieved from the element will be translated; if
     * either is not the case, they will not.
     *
     * @param  array $attributes
     * @throws Exception\DomainException
     */
    public function render(ElementInterface $element, array $attributes = []): string
    {
        $messages = $element->getMessages();
        if (! $messages) {
            return '';
        }

        // Flatten message array
        $messages = $this->flattenMessages($messages);
        if (! $messages) {
            return '';
        }

        // Prepare attributes for opening tag
        $attributes = array_merge($this->attributes, $attributes);
        $attributes = $this->createAttributesString($attributes);
        if (! empty($attributes)) {
            $attributes = ' ' . $attributes;
        }

        // Generate markup
        $markup  = sprintf($this->getMessageOpenFormat(), $attributes);
        $markup .= implode($this->getMessageSeparatorString(), $messages);
        $markup .= $this->getMessageCloseString();

        return $markup;
    }

    /**
     * Set the attributes that will go on the message open format
     *
     * @param  array $attributes key value pairs of attributes
     * @return $this
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * Get the attributes that will go on the message open format
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Set the string used to close message representation
     *
     * @param  string $messageCloseString
     * @return $this
     */
    public function setMessageCloseString($messageCloseString)
    {
        $this->messageCloseString = (string) $messageCloseString;
        return $this;
    }

    /**
     * Get the string used to close message representation
     */
    public function getMessageCloseString(): string
    {
        return $this->messageCloseString;
    }

    /**
     * Set the formatted string used to open message representation
     *
     * @param  string $messageOpenFormat
     * @return $this
     */
    public function setMessageOpenFormat($messageOpenFormat)
    {
        $this->messageOpenFormat = (string) $messageOpenFormat;
        return $this;
    }

    /**
     * Get the formatted string used to open message representation
     */
    public function getMessageOpenFormat(): string
    {
        return $this->messageOpenFormat;
    }

    /**
     * Set the string used to separate messages
     *
     * @param  string $messageSeparatorString
     * @return $this
     */
    public function setMessageSeparatorString($messageSeparatorString)
    {
        $this->messageSeparatorString = (string) $messageSeparatorString;
        return $this;
    }

    /**
     * Get the string used to separate messages
     */
    public function getMessageSeparatorString(): string
    {
        return $this->messageSeparatorString;
    }

    /**
     * Set the flag detailing whether or not to translate error messages.
     *
     * @param bool $flag
     * @return $this
     */
    public function setTranslateMessages($flag)
    {
        $this->translateErrorMessages = (bool) $flag;
        return $this;
    }

    /**
     * @param array $messages
     * @return array
     */
    private function flattenMessages(array $messages): array
    {
        return $this->translateErrorMessages && $this->getTranslator()
            ? $this->flattenMessagesWithTranslator($messages)
            : $this->flattenMessagesWithoutTranslator($messages);
    }

    /**
     * @param array $messages
     * @return array
     */
    private function flattenMessagesWithoutTranslator(array $messages): array
    {
        $messagesToPrint = [];
        array_walk_recursive($messages, static function ($item) use (&$messagesToPrint) {
            $messagesToPrint[] = $item;
        });
        return $messagesToPrint;
    }

    /**
     * @param array $messages
     * @return array
     */
    private function flattenMessagesWithTranslator(array $messages): array
    {
        $translator      = $this->getTranslator();
        $textDomain      = $this->getTranslatorTextDomain();
        $messagesToPrint = [];
        $messageCallback = static function ($item) use (&$messagesToPrint, $translator, $textDomain): void {
            $messagesToPrint[] = $translator->translate($item, $textDomain);
        };
        array_walk_recursive($messages, $messageCallback);
        return $messagesToPrint;
    }
}
