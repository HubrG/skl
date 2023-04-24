<?php
// src/Service/HtmlToEpubConverter.php

namespace App\Services;

use PHPePub\Core\EPub;

class HtmlToEpubConverter
{
    public function convert(string $html, string $title, string $author): EPub
    {
        $epub = new EPub();

        // Configure les métadonnées du livre électronique
        $epub->setTitle($title);
        $epub->setAuthor($author, $author);
        $epub->setPublisher("Your Publisher", "https://www.your-publisher.com");
        $epub->setIdentifier("https://www.your-publisher.com/books/" . $title, EPub::IDENTIFIER_URI);
        $epub->setDate(time());
        $epub->setRights("Tous droits réservés");
        $epub->setSourceURL("https://www.your-publisher.com/books/" . $title);

        // Ajoute le contenu HTML en tant que chapitre
        $epub->addChapter("Chapitre 1", "Chapter001.html", $html);

        // Finalise le livre électronique et renvoie l'objet EPub
        $epub->finalize();
        return $epub;
    }
}
