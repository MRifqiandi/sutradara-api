<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
  $app->get('/penghargaan', function (Request $request, Response $response) {
    $db = $this->get(PDO::class);

    $query = $db->query('CALL GetPenghargaan'); 
    $results = $query->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($results));

    return $response->withHeader("Content-Type", "application/json");
});

$app->get('/penghargaan/{id_penghargaan}', function (Request $request, Response $response, $args) {
  $db = $this->get(PDO::class);
  $id_judul = $args['id_penghargaan'];

  $query = $db->prepare('CALL GetPenghargaanById(?)');
  $query->execute([$id_judul]);

  if ($query->rowCount() === 0) {
      $response = $response->withStatus(404);
      $response->getBody()->write(json_encode([
          'message' => 'Penghargaan dengan ID ' . $id_judul . ' tidak ditemukan'
      ]));
  } else {
      $results = $query->fetchAll(PDO::FETCH_ASSOC);
      $response->getBody()->write(json_encode($results[0]));
  }

  return $response->withHeader("Content-Type", "application/json");
});

$app->post('/penghargaan/create', function (Request $request, Response $response) {
  $parsedBody = $request->getParsedBody();

  $id_judul = $parsedBody["id_judul"];
  $penghargaan = $parsedBody["penghargaan"];

  $db = $this->get(PDO::class);

  $query = $db->prepare('CALL AddPenghargaan(?, ?)');

  $query->execute([$id_judul, $penghargaan]);

  $lastId = $db->lastInsertId();

  $response->getBody()->write(json_encode(
      [
          'message' => 'Penghargaan disimpan dengan id ' . $lastId
      ]
  ));

  return $response->withHeader("Content-Type", "application/json");
});

$app->put('/penghargaan/{id_penghargaan}/update', function (Request $request, Response $response, $args) {
  $parsedBody = $request->getParsedBody();
  $currentId = $args['id_penghargaan'];
  $newPenghargaan = $parsedBody["penghargaan"];

  $db = $this->get(PDO::class);

  // Perbarui pemanggilan prosedur untuk menggunakan 'CALL UpdatePenghargaan'
  $query = $db->prepare('CALL UpdatePenghargaan(?, ?)');
  $query->execute([$currentId, $newPenghargaan]);

  $response->getBody()->write(json_encode(
      [
          'message' => 'Penghargaan dengan ID ' . $currentId . ' telah diperbarui'
      ]
  ));

  return $response->withHeader("Content-Type", "application/json");
});

$app->delete('/penghargaan/{id_penghargaan}/delete', function (Request $request, Response $response, $args) {
  // Dapatkan id_penghargaan dari parameter URL
  $id_penghargaan = $args['id_penghargaan'];

  // Dapatkan koneksi ke database
  $db = $this->get(PDO::class);

  try {
      // Siapkan dan eksekusi perintah SQL untuk memanggil procedure
      $query = $db->prepare('CALL DeletePenghargaan(?)');
      $query->execute([$id_penghargaan]);

      // Periksa apakah data berhasil dihapus
      if ($query->rowCount() > 0) {
          // Data berhasil dihapus
          $response->getBody()->write(json_encode(['message' => 'Penghargaan dengan ID ' . $id_penghargaan . ' telah dihapus.']));
      } else {
          // Data tidak ditemukan
          $response = $response->withStatus(404);
          $response->getBody()->write(json_encode(['message' => 'Penghargaan dengan ID ' . $id_penghargaan . ' tidak ditemukan.']));
      }
  } catch (PDOException $e) {
      // Tangani kesalahan database
      $response = $response->withStatus(500);
      $response->getBody()->write(json_encode(['message' => 'Terjadi kesalahan database: ' . $e->getMessage()]));
  }

  return $response->withHeader('Content-Type', 'application/json');
});

};    

