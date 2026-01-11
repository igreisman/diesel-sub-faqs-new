<?php

require_once __DIR__.'/../config/database.php';

try {
    $pdo->beginTransaction();

    $sql = 'INSERT INTO lost_submarines (boat_number, name, designation, class_info, last_captain, date_lost, location, fatalities, cause, loss_narrative, prior_history, era, year_lost, photo_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $stmt = $pdo->prepare($sql);

    $vals = [
        'SS-23',
        'F-4',
        'USS F-4 (SS-23)',
        'F class submarine completed in May of 1913 by the Moran Company of Seattle, WA. Originally named the Skate.',
        'LTJG Alfred L. Ede',
        '1915-03-25',
        'Off Honolulu, TH',
        'All 21 crew aboard.',
        'Foundered',
        "The boat foundered during local operations. The cause is not clear. The salvage team estimated that sea water was leaking through a lead seal and into the battery compartment. Another possible cause was an unreliable reducer closing a Kingston valve. A third possibility would be problems in the ballast tank air lines.\n\nOne sailor was left ashore as a watchman. His duties were to receive any important news while the boat was at sea, and relay it to the captain on his return. This was often done before radios were installed on boats. His status regarding the sinking is often misstated as a survivor.",
        "In August of 1915, F-4 was raised and towed using specially constructed pontoons. She was then put into dry dock for examination. In early September, she had to be removed from dry dock to accommodate three other F class boats that had been rammed by the USS Supply. F-4 was still hanging from the pontoons. She was then disconnected and left in the mud near Pearl Harbor. In 1940, due to an expansion of the base, the F-4 was moved and buried near the submarine piers.\n\nF-4 was the first commissioned U. S. submarine to be lost at sea.",
        'wwi',
        1915,
        null,
    ];

    $stmt->execute($vals);
    $id = $pdo->lastInsertId();
    $pdo->commit();

    echo "Inserted USS F-4 with id: {$id}\n";

    // Verify
    $v = $pdo->prepare('SELECT id, boat_number, name, designation, date_lost, era FROM lost_submarines WHERE id = ? LIMIT 1');
    $v->execute([$id]);
    $row = $v->fetch();
    if ($row) {
        echo json_encode($row, JSON_PRETTY_PRINT)."\n";

        exit(0);
    }
    echo "Insert reported ID {$id} but row not found.\n";

    exit(2);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo 'Insert failed: '.$e->getMessage()."\n";

    exit(1);
}
