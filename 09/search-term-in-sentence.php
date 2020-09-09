<?php

$sentence = "清晨很想去游一下，到了游泳池，才發現沒帶泳褲。";
$anchorPosition = 12; // 泳

$dictTerms = array(
   "晨泳",
   "游泳",
   "游泳池",
   "泳池游",
   "泳池",
   "泳褲",
   "泳客"
);

function buildInvertedIndex($sentence) {
  $index = array();
  for ($i = 0; $i < mb_strlen($sentence, "utf-8"); $i++) {
    $char = mb_substr($sentence, $i, 1, "utf-8");
    if (array_key_exists($char, $index) === false) {
      $index[$char] = array();
    }
    array_push($index[$char], $i);
  }
  return $index;
}

function buildTermsTable($terms) {
  $table = array();
  for ($i = 0; $i < count($terms); $i++) {
    $term = $terms[$i];
    $table[] = array(
      "term" => $term
    );
  }
  return $table;
}

function filterByOccurs($sentenceIndex, $anchorPosition, $term) {
  $matchedAnchorPosition = false;
  $positions = array();
  
  for ($i = 0; $i < mb_strlen($term, "utf-8"); $i++) {
    $word = mb_substr($term, $i, 1, "utf-8");

    if (array_key_exists($word, $sentenceIndex) === false) {
      // 一旦有一個字沒有找到，那就表示這個詞彙不是我們要的詞彙
      return false;
    }
    $wordPositionsInSentence = $sentenceIndex[$word];
    
    if (array_search($anchorPosition, $wordPositionsInSentence) !== false) {
      $matchedAnchorPosition = true;
    }
    
    $positions = array_merge($positions, $wordPositionsInSentence);
  }

  if ($matchedAnchorPosition === false) {
    // 表示找到的詞彙跟現在要查詢的字無關
    return false;
  }

  if (count($positions) === 0) {
    // 表示都沒找到
    return false;
  }
  return $positions;
}

function filterBySequence($anchorPosition, $termLength, $positions) {
  sort($positions);

  $positionSequence = array();
  $matchedAnchorPosition = false;
  
  for ($i = 0; $i < count($positions); $i++) {
    $position = $positions[$i];
    if ($position === $anchorPosition) {
      $matchedAnchorPosition = true;
    }

    if ($i === 0) {
      $positionSequence[] = $position;
      continue;
    }

    $lastPosition = $positions[($i-1)];
    if ($position - $lastPosition === 1) {
      // 表示是連續字數
      $positionSequence[] = $position;

      if (count($positionSequence) === $termLength
            && $matchedAnchorPosition === true) {
        // 符合現在要查詢的詞彙
        return $positionSequence;
      }
    }
    else {
      $positionSequence = array($position);

      if ($matchedAnchorPosition === true) {
        return false;
      }
    }
  }

  return false;
}

function filterTermSequence($sentence, $positionSequence, $term) {
  $wordList = array();
  for ($i = 0; $i < count($positionSequence); $i++) {
    $position = $positionSequence[$i];
    $wordList[] = mb_substr($sentence, $position, 1, "utf-8");
  }
  $word = implode("", $wordList);
  return ($word === $term);
}

function sortTerms($termsTable, $anchorPosition) {
  usort($termsTable, function ($a, $b) use($anchorPosition)  {
    $termLengthA = mb_strlen($a["term"], "utf-8");
    $termLengthB = mb_strlen($b["term"], "utf-8");
    if ($termLengthA !== $termLengthB) {
      return ($termLengthB - $termLengthA);
    }
    else {
      $anchorPositionInA = array_search($anchorPosition, $a["positions"]);
      $anchorPositionInB = array_search($anchorPosition, $b["positions"]);
      return $anchorPositionInA - $anchorPositionInB;
    }
  });
  return $termsTable;
}

function main($sentence, $anchorPosition, $dictTerms) {
  $sentenceIndex = buildInvertedIndex($sentence);
  $termsTable = buildTermsTable($dictTerms);

  $tempTermsTable = array();
  for ($i = 0; $i < count($termsTable); $i++) {
    $item = $termsTable[$i];
    
    $positions = filterByOccurs($sentenceIndex, $anchorPosition, $item["term"]);
    if ($positions === false) {
      continue;
    }
    
    $item["positions"] = $positions;
    $tempTermsTable[] = $item;
  }
  $termsTable = $tempTermsTable;

  $tempTermsTable = array();
  for ($i = 0; $i < count($termsTable); $i++) {
    $item = $termsTable[$i];
    
    $termLength = mb_strlen($item["term"], "utf-8");
    $positionSequence = filterBySequence($anchorPosition, $termLength, $item["positions"]);
    if ($positionSequence === false) {
      continue;
    }
    $item["positions"] = $positionSequence;
    $tempTermsTable[] = $item;
  }
  $termsTable = $tempTermsTable;

  $tempTermsTable = array();
  for ($i = 0; $i < count($termsTable); $i++) {
    $item = $termsTable[$i];
    
    if (filterTermSequence($sentence, $item["positions"], $item["term"]) === false) {
      continue;
    }
    $tempTermsTable[] = $item;
  }
  $termsTable = $tempTermsTable;

  $sortedFilterTerms = sortTerms($termsTable, $anchorPosition);
  $sortedFilterTerms = array_map(function ($item) {
    return $item["term"];
  }, $sortedFilterTerms);
  
  print_r($sortedFilterTerms);
}

// 開始執行
main($sentence, $anchorPosition, $dictTerms);
