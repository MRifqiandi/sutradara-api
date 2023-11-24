<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
  
  $app->get('/pendapatan', function (Request $request, Response $response) {
    $db = $this->get(PDO::class);

    $query = $db->query('CALL GetPendapatanAll'); 
    $results = $query->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($results));

    return $response->withHeader("Content-Type", "application/json");
});

$app->get('/pendapatan/{id_pendapatan}', function (Request $request, Response $response, $args) {
  $db = $this->get(PDO::class);
  $id_judul = $args['id_pendapatan'];

  $query = $db->prepare('CALL GetPendapatanById(?)');
  $query->execute([$id_judul]);

  if ($query->rowCount() === 0) {
      $response = $response->withStatus(404);
      $response->getBody()->write(json_encode([
          'message' => 'Pendapatan dengan ID ' . $id_judul . ' tidak ditemukan'
      ]));
  } else {
      $results = $query->fetchAll(PDO::FETCH_ASSOC);
      $response->getBody()->write(json_encode($results[0]));
  }

  return $response->withHeader("Content-Type", "application/json");
});


$app->post('/pendapatan/create', function (Request $request, Response $response) {
  $parsedBody = $request->getParsedBody();

  $id_sutradara = $parsedBody["id_sutradara"];
  $id_judul = $parsedBody["id_judul"];
  $pendapatan = $parsedBody["pendapatan"];

  $db = $this->get(PDO::class);

  $query = $db->prepare('CALL AddPendapatan(?, ?, ?)');

  $query->execute([$id_sutradara, $id_judul, $pendapatan]);

  $lastId = $db->lastInsertId();

  $response->getBody()->write(json_encode(
      [
          'message' => 'Pendapatan disimpan dengan id ' . $lastId
      ]
  ));

  return $response->withHeader("Content-Type", "application/json");
});

$app->put('/pendapatan/{id_pendapatan}/update', function (Request $request, Response $response, $args) {
  $parsedBody = $request->getParsedBody();
  $currentId = $args['id_pendapatan'];
  $newPendapatan = $parsedBody["pendapatan"];

  $db = $this->get(PDO::class);

  // Perbarui pemanggilan prosedur untuk menggunakan 'CALL UpdatePendapatan'
  $query = $db->prepare('CALL UpdatePendapatan(?, ?)');
  $query->execute([$currentId, $newPendapatan]);

  $response->getBody()->write(json_encode(
      [
          'message' => 'Pendapatan dengan ID ' . $currentId . ' telah diperbarui'
      ]
  ));

  return $response->withHeader("Content-Type", "application/json");
});

$app->delete('/pendapatan/{id_pendapatan}/delete', function (Request $request, Response $response, $args) {
  // Dapatkan id_pendapatan dari parameter URL
  $id_pendapatan = $args['id_pendapatan'];

  // Dapatkan koneksi ke database
  $db = $this->get(PDO::class);

  try {
      // Siapkan dan eksekusi perintah SQL untuk memanggil procedure
      $query = $db->prepare('CALL DeletePendapatan(?)');
      $query->execute([$id_pendapatan]);

      // Periksa apakah data berhasil dihapus
      if ($query->rowCount() > 0) {
          // Data berhasil dihapus
          $response->getBody()->write(json_encode(['message' => 'Pendapatan dengan ID ' . $id_pendapatan . ' telah dihapus.']));
      } else {
          // Data tidak ditemukan
          $response = $response->withStatus(404);
          $response->getBody()->write(json_encode(['message' => 'Pendapatan dengan ID ' . $id_pendapatan . ' tidak ditemukan.']));
      }
  } catch (PDOException $e) {
      // Tangani kesalahan database
      $response = $response->withStatus(500);
      $response->getBody()->write(json_encode(['message' => 'Terjadi kesalahan database: ' . $e->getMessage()]));
  }

  return $response->withHeader('Content-Type', 'application/json');
});

};