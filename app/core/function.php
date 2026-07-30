<?php

function query($conn, $query, $data = [])
{
    $stmt = $conn-> prepare($query);
    $stmt->execute($data);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $result;
}

function execute($conn, $query, $data = [])
{
    $stmt = $conn->prepare($query);
    return $stmt->execute($data);
}
