# LogFile
#### A library for working with log files (or any file, really)

Example 1 : Using the Iterator interface

function main(): void {
  $logFile = new LogAlerts\LogFile('path/to/file');
  $logFileText = '';
  
  foreach ($logFile as $lineNumber => $line) {
    if ($line !== '') {
        $logFileText .= $line;
    }
  }
}
