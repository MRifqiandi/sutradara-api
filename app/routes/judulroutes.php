<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
  $app->get('/judul', function (Request $request, Response $response) {
    $db = $this->get(PDO::class);

    $query = $db->query('CALL GetJudul'); 
    $results = $query->fetchAll(PDO::FETCH_ASSOC);
    $response->getBody()->write(json_encode($results));

    return $response->withHeader("Content-Type", "application/json");
});

$app->get('/judul/{id_judul}', function (Request $request, Response $response, $args) {
  $db = $this->get(PDO::class);
  $id_judul = $args['id_judul'];

  $query = $db->prepare('CALL GetJudulById(?)');
  $query->execute([$id_judul]);

  if ($query->rowCount() === 0) {
      $response = $response->withStatus(404);
      $response->getBody()->write(json_encode([
          'message' => 'Judul dengan ID ' . $id_judul . ' tidak ditemukan'
      ]));
  } else {
      $results = $query->fetchAll(PDO::FETCH_ASSOC);
      $response->getBody()->write(json_encode($results[0]));
  }

  return $response->withHeader("Content-Type", "application/json");
});

$app->post('/judul/create', function (Request $request, Response $response) {
  $parsedBody = $request->getParsedBody();

  $id_sutradara = $parsedBody["id_sutradara"]; 
  $judul = $parsedBody["judul"];

  $db = $this->get(PDO::class);

  $query = $db->prepare('CALL InsertJudul(?, ?)'); // Sesuaikan dengan nama stored procedure yang benar

  $query->execute([$id_sutradara, $judul]);

  $lastId = $db->lastInsertId();

  $response->getBody()->write(json_encode(
      [
          'message' => 'Judul disimpan dengan id ' . $lastId
      ]
  ));

  return $response->withHeader("Content-Type", "application/json");
});

$app->put('/judul/{id_judul}/update', function (Request $request, Response $response, $args) {
  $parsedBody = $request->getParsedBody();
  $currentId = $args['id_judul'];
  $new_judul = $parsedBody["judul"]; // Gunakan "judul" sebagai parameter yang sesuai dengan prosedur SQL
  
  $db = $this->get(PDO::class);
  
  $query = $db->prepare('CALL Update_Judul(?, ?)'); // Sesuaikan parameter yang sesuai dengan prosedur SQL
  $query->execute([$currentId, $new_judul]);
  
  $response->getBody()->write(json_encode(
      [
          'message' => 'Judul dengan ID ' . $currentId . ' telah diperbarui'
      ]
  ));
  
  return $response->withHeader("Content-Type", "application/json");
});

$app->delete('/judul/{id_judul}/delete', function (Request $request, Response $response, $args) {
  // Dapatkan id_judul dari parameter URL
  $id_judul = $args['id_judul'];
  
  // Dapatkan koneksi ke database
  $db = $this->get(PDO::class);

  try {
      // Siapkan dan eksekusi perintah SQL untuk memanggil procedure
      $query = $db->prepare('CALL delete_judul(?)');
      $query->execute([$id_judul]);

      // Periksa apakah data berhasil dihapus
      if ($query->rowCount() > 0) {
          // Data berhasil dihapus
          $response->getBody()->write(json_encode(['message' => 'Judul dengan ID ' . $id_judul . ' telah dihapus.']));
      } else {
          // Data tidak ditemukan
          $response = $response->withStatus(404);
          $response->getBody()->write(json_encode(['message' => 'Judul dengan ID ' . $id_judul . ' tidak ditemukan.']));
      }
  } catch (PDOException $e) {
      // Tangani kesalahan database
      $response = $response->withStatus(500);
      $response->getBody()->write(json_encode(['message' => 'Terjadi kesalahan database: ' . $e->getMessage()]));
  }

  return $response->withHeader('Content-Type', 'application/json');
});

};
