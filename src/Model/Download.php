<?php

namespace Model;


class Download
{


  public string $file;

  public string $fileSize;

  public int $errorCode;
  public function downloadPdfFile(): void
  {

    $this->file = "../public/uploads/test.pdf";

    if (!file_exists($this->file)) {
      $this->errorCode = 500;
      header("HTTP/1.1 302");
      header("Location:?action=error&code=" . $this->errorCode);
    }

    $this->fileSize = filesize($this->file);

    header("Content-Length: " . $this->fileSize);
    header('Content-Description: File Transfer');
    header("Content-Type: application/pdf");
    header("Pragma: public");
    header("Content-Disposition:attachment;filename=cv.pdf");
    header("HTTP/1.1 200");
    readfile($this->file);
  }
}
