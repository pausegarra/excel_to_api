<?php

require_once './vendor/autoload.php';

use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;

class Main
{
  private $date;
  private $rows;
  private $headers;
  private $content;
  private $token;
  private $data;
  private $logs;

  const URL_BASE = 'http://localhost:8000/';
  const URL_FINAL = self::URL_BASE . '';
  const ENDPOINT = "";

  const USERNAME = '';
  const PASSWORD = '';

  public function __construct()
  {
    $this->date = date('Ymd_His');
    $this->data = [];

    $this->run();
  }

  private function run()
  {
    $this->checkDirs();
    $this->parseFile();
    $this->getContent();
    $this->apiLogin();
    $this->createRecords();
    $this->saveExcel();
    $this->moveDataExcel();
    $this->saveLogs();
  }

  /**
   * Parse the xlsx file to get headers and content
   *
   * @return void
   */
  private function parseFile(): void
  {
    $xlsx = SimpleXLSX::parse('data.xlsx');
    $this->rows = $xlsx->rows();

    $this->headers = $this->getHeaders();
    $this->content = $this->rows;
  }

  /**
   * Get the hedaers of the rows parsed previously
   *
   * @return array
   */
  private function getHeaders(): array
  {
    $headers = array_shift($this->rows);

    foreach ($headers as $key => $value) {
      $headers[$key] = strtolower(str_replace(' ', '_', $value));
    }

    return $headers;
  }

  /**
   * Combine headers and rows to match the key with the value
   *
   * @return void
   */
  private function getContent(): void
  {
    $content = [];

    foreach ($this->rows as $row) {
      $content[] = array_combine($this->headers, $row);
    }

    $this->content = $content;
  }

  /**
   * Login into the API to get the token
   *
   * @return void
   */
  private function apiLogin(): void
  {
    $credentials = [
      'username' => self::USERNAME,
      'password' => self::PASSWORD
    ];
    $url = self::URL . 'login';
    $this->token = $this->post($url, $credentials)['token'];
  }

  /**
   * Loop into the content to create the records
   *
   * @return void
   */
  private function createRecords(): void
  {
    foreach ($this->content as $record) {
      $this->createRecord($record);
    }
  }

  /**
   * Create the request to the API for hihglight creation
   *
   * @param array $data
   * @return void
   */
  private function createRecord($data): void
  {
    $url = self::URL_BASE . self::ENDPOINT;
    $res = $this->post($url, $data, $this->token);

    if ($res['status'] !== 201 && $res['status'] !== 200) {
      $this->logs[] = "Errror creating register: " . $res['message'];
      return;
    }

    $res['data']['landing'] = $this->generateUrl($res['data']);

    $this->data[] = $res['data'];
    $this->logs[] = "Register with id {$res['data']['id']} created";
  }

  /**
   * Generates the URL needed to store in Excel
   *
   * @param array $data
   * @return string
   */
  private function generateUrl($data): string
  {
    return self::URL_FINAL . $data['slug'];
  }

  /**
   * Saves the result to excel
   *
   * @return void
   */
  private function saveExcel(): void
  {
    SimpleXLSXGen::fromArray($this->data)->saveAs('./xlsx/generated/' . $this->date . '.xlsx');
  }

  /**
   * Moves the excel to the old irectory
   * 
   * @return void
   */
  private function moveDataExcel(): void
  {
    rename('./data.xlsx', './xlsx/old/' . $this->date . '.xlsx');
  }

  /**
   * Save the logs
   * 
   * @return void
   */
  private function saveLogs(): void
  {
    file_put_contents('./logs/' . $this->date . '.log', implode("\n", $this->logs));
  }

  private function checkDirs(): void
  {
    if (!file_exists('./xlsx')) {
      mkdir('./xlsx');
    }

    if (!file_exists('./xlsx/generated')) {
      mkdir('./xlsx/generated');
    }

    if (!file_exists('./xlsx/old')) {
      mkdir('./xlsx/old');
    }

    if (!file_exists('./logs')) {
      mkdir('./logs');
    }
  }

  /**
   * Perform a POST request to the API
   *
   * @param string $url
   * @param array $data
   * @param string $token
   * @return string|array
   */
  private function post($url, $data, $token = null)
  {
    $curl = curl_init();

    $header[] = 'Content-Type: application/json';
    $header[] = 'Accept: application/json';

    if ($token !== null) {
      $header[] = 'Authorization: Bearer ' . $token;
    }

    curl_setopt_array($curl, [
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => json_encode($data),
      CURLOPT_HTTPHEADER => $header,
    ]);

    $response = json_decode(curl_exec($curl), true);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    $response['status'] = $status;

    return $response;
  }
}

new Main();
