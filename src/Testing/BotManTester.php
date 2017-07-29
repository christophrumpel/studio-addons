<?php

namespace BotMan\Studio\Testing;

use BotMan\BotMan\BotMan;
use PHPUnit\Framework\Assert as PHPUnit;
use BotMan\BotMan\Drivers\Tests\FakeDriver;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;

/**
 * Class BotManTester.
 */
class BotManTester
{
    /** @var BotMan */
    private $bot;

    /** @var FakeDriver */
    private $driver;

    /** @var string */
    private $username = 'botman';

    /** @var string */
    private $channel = '#botman';

    /**
     * BotManTester constructor.
     *
     * @param BotMan $bot
     * @param FakeDriver $driver
     */
    public function __construct(BotMan $bot, FakeDriver $driver)
    {
        $this->bot = $bot;
        $this->driver = $driver;
    }

    protected function listen()
    {
        $this->bot->listen();
        $this->driver->isInteractiveMessageReply = false;
    }

    /**
     * @return OutgoingMessage
     */
    protected function getReply()
    {
        $this->listen();
        $messages = $this->getMessages();

        return array_pop($messages);
    }

    /**
     * @param $driver
     * @return $this
     */
    public function usingDriver($driver)
    {
        return $this;
    }

    /**
     * @param $message
     * @return $this
     */
    public function receives($message)
    {
        $this->driver->messages = [new IncomingMessage($message, $this->username, $this->channel)];

        return $this;
    }

    /**
     * @param $message
     * @return BotManTester
     */
    public function receivesInteractiveMessage($message)
    {
        $this->driver->isInteractiveMessageReply = true;

        return $this->receives($message);
    }

    /**
     * @param $text
     */
    public function assertReply($text)
    {
        PHPUnit::assertSame($this->getReply()->getText(), $text);
    }

    /**
     * Assert that there are specific multiple replies.
     *
     * @param array $expectedMessages
     */
    public function assertReplies($expectedMessages)
    {
        $this->listen();
        $actualMessages = $this->getMessages();

        foreach ($actualMessages as $key => $actualMessage) {
            PHPUnit::assertSame($expectedMessages[$key], $actualMessage->getText());
        }
    }

    /**
     * @param $text
     */
    public function assertReplyIsNot($text)
    {
        PHPUnit::assertNotSame($this->getReply()->getText(), $text);
    }

    /**
     * @param array $haystack
     */
    public function assertReplyIn(array $haystack)
    {
        PHPUnit::assertTrue(in_array($this->getReply()->getText(), $haystack));
    }

    /**
     * @param array $haystack
     */
    public function assertReplyNotIn(array $haystack)
    {
        PHPUnit::assertFalse(in_array($this->getReply()->getText(), $haystack));
    }

    /**
     * @param null $text
     * @return $this
     */
    public function assertQuestion($text = null)
    {
        $this->listen();
        $messages = $this->getMessages();

        /** @var Question $question */
        $question = array_pop($messages);
        PHPUnit::assertInstanceOf(Question::class, $question);

        if (! is_null($text)) {
            PHPUnit::assertSame($question->getText(), $text);
        }

        return $this;
    }

    /**
     * @return Question[]|\string[]
     */
    public function getMessages()
    {
        return $this->driver->getBotMessages();
    }
}
