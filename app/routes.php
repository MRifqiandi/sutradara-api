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

    $app->get('/judul', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);
    
        $query = $db->query('CALL GetJudul'); 
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));
    
        return $response->withHeader("Content-Type", "application/json");
    });

    $app->get('/pendapatan', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);
    
        $query = $db->query('CALL GetPendapatanAll'); 
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));
    
        return $response->withHeader("Content-Type", "application/json");
    });

    $app->get('/penghargaan', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);
    
        $query = $db->query('CALL GetPenghargaan'); 
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

