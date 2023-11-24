<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {

    // get
    $app->get('/sutradara', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        $query = $db->query('CALL GetSutradara');
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));

        return $response->withHeader("Content-Type", "application/json");
    });
    
    // get by id
    $app->get('/sutradara/{id_sutradara}', function (Request $request, Response $response, $args) {
        $db = $this->get(PDO::class);

        $query = $db->prepare('CALL GetSutradaraById(?)');
        $query->execute([$args['id_sutradara']]);
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results[0]));

        return $response->withHeader("Content-Type", "application/json");
    });

    // post data
    $app->post('/sutradara/create', function (Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();
    
        // $id = $parsedBody["id_sutradara"];
        $nama_sutradara = $parsedBody["nama_sutradara"];
        $kebangsaan = $parsedBody["Kebangsaan"];
        $perusahaan = $parsedBody["perusahaan"];
    
        $db = $this->get(PDO::class);
    
        $query = $db->prepare('CALL AddSutradara(?, ?, ?)'); // Perbaikan disini
    
        $query->execute([$nama_sutradara, $kebangsaan, $perusahaan]);
    
        $lastId = $db->lastInsertId();
    
        $response->getBody()->write(json_encode(
            [
                'message' => 'Sutradara disimpan dengan id ' . $lastId
            ]
        ));
    
        return $response->withHeader("Content-Type", "application/json");
    });

    // put data
    $app->put('/sutradara/{id_sutradara}/update', function (Request $request, Response $response, $args) {
        $parsedBody = $request->getParsedBody();
        $currentId = $args['id_sutradara'];
        $nama_sutradara = $parsedBody["nama_sutradara"];
        $kebangsaan = $parsedBody["Kebangsaan"];
        $perusahaan = $parsedBody["perusahaan"];
    
        $db = $this->get(PDO::class);
        
        // Perbarui pemanggilan prosedur untuk menggunakan 'CALL UpdateSutradara'
        $query = $db->prepare('CALL UpdateSutradara(?, ?, ?, ?)');
        $query->execute([$currentId, $nama_sutradara, $kebangsaan, $perusahaan]);
    
        $response->getBody()->write(json_encode(
            [
                'message' => 'Sutradara dengan ID ' . $currentId . ' telah diperbarui'
            ]
        ));
    
        return $response->withHeader("Content-Type", "application/json");
    });

    // delete data
    $app->delete('/sutradara/{id_sutradara}/delete', function (Request $request, Response $response, $args) {
        $currentId = $args['id_sutradara'];
        $db = $this->get(PDO::class);
    
        try {
            $query = $db->prepare('CALL DeleteSutradara(?)'); // Memanggil prosedur DeleteSutradara
            $query->execute([$currentId]);
    
            if ($query->rowCount() === 0) {
                $response = $response->withStatus(404);
                $response->getBody()->write(json_encode(
                    [
                        'message' => 'Data tidak ditemukan'
                    ]
                ));
            } else {
                $response->getBody()->write(json_encode(
                    [
                        'message' => 'Sutradara dengan ID ' . $currentId . ' telah dihapus dari database'
                    ]
                ));
            }
        } catch (PDOException $e) {
            $response = $response->withStatus(500);
            $response->getBody()->write(json_encode(
                [
                    'message' => 'Database error ' . $e->getMessage()
                ]
            ));
        }
    
        return $response->withHeader("Content-Type", "application/json");
    });

  };
