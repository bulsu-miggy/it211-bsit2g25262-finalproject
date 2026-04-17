<?php

require __DIR__ . '/../db/action/dbconfig.php'; // reuse the shared PDO connection

$stmt = $conn->query(
    "SELECT id, title, category, author, publish_date, price, media, media_type FROM books"
);
$booksArray = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($booksArray)) {
    createXMLfile($booksArray);
}

// ─────────────────────────────────────────────
function createXMLfile(array $booksArray): void
{
    $filePath = '../xmlfiles/book.xml';

    if (!file_exists($filePath)) {
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true;

        $impl = new DOMImplementation();
        $dom->appendChild($impl->createDocumentType('books', '', 'Bookstore.dtd'));

        $root = $dom->createElement('books');

        foreach ($booksArray as $book) {
            $bookNode = $dom->createElement('book');
            $bookNode->setAttribute('category', $book['category']);

            $bookNode->appendChild($dom->createElement('bookId',      $book['id']));
            $bookNode->appendChild($dom->createElement('title',       htmlspecialchars($book['title'])));
            $bookNode->appendChild($dom->createElement('author',      $book['author']));
            $bookNode->appendChild($dom->createElement('year',        $book['publish_date']));
            $bookNode->appendChild($dom->createElement('price',       $book['price']));

            $mediaNode = $dom->createElement('media', $book['media']);
            $mediaNode->setAttribute('type', $book['media_type']);
            $bookNode->appendChild($mediaNode);

            $root->appendChild($bookNode);
        }

        $dom->appendChild($root);
        $dom->save($filePath);
        echo "XML created successfully!\n";
    } else {
        echo "XML file already exists.\n";
    }

    validateXML($filePath);
}

function validateXML(string $xml): void
{
    try {
        if (file_exists($xml)) {
            $dom = new DOMDocument();
            $dom->load($xml);
            echo $dom->validate() ? "Document is valid!\n" : "Document is NOT valid — check your DTD.\n";
        } else {
            echo "File not found: $xml\n";
        }
    } catch (Exception $e) {
        echo "Validation error: " . $e->getMessage() . "\n";
    }
}
