<?php

namespace PHPdfer;

class MetadataBuilder
{
    public function setFirstCharacter(): string
    {
        return '[' . PHP_EOL;
    }

    public function setTitle(string $title): string
    {
        return "/Title ($title)" . PHP_EOL;
    }

    public function setAuthor(string $author): string
    {
        return "/Author ($author)" . PHP_EOL;
    }

    public function setSubject(string $subject): string
    {
        return "/Subject ($subject)" . PHP_EOL;
    }

    public function setKeywords(string $keywords): string
    {
        return "/Keywords ($keywords)" . PHP_EOL;
    }

    public function setModDate(string $modDate): string
    {
        return "/ModDate (D:$modDate)" . PHP_EOL;
    }

    public function setCreationDate(string $creationDate): string
    {
        return "/CreationDate (D:$creationDate)" . PHP_EOL;
    }

    public function setCreator(string $creator = 'PHPdfer'): string
    {
        return "/Creator ($creator)" . PHP_EOL;
    }


    public function setLastCharacter(): string
    {
        return '/DOCINFO pdfmark';
    }
}
