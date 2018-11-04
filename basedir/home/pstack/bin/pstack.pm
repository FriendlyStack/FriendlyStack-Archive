package pstack;


##FriendlyStack, a system for managing physical and electronic documents as well as photos and videos
##Copyright (C) 2018  Dimitrios F. Kallivroussis, Friendly River LLC
##
##This program is free software: you can redistribute it and/or modify
##it under the terms of the GNU Affero General Public License as
##published by the Free Software Foundation, either version 3 of the
##License, or (at your option) any later version.
##
##This program is distributed in the hope that it will be useful,
##but WITHOUT ANY WARRANTY; without even the implied warranty of
##MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
##GNU Affero General Public License for more details.
##
##You should have received a copy of the GNU Affero General Public License
##along with this program.  If not, see <http://www.gnu.org/licenses/>.


use Exporter;
use vars qw($VERSION @ISA @EXPORT @EXPORT_OK %EXPORT_TAGS);

$VERSION     = 1.00;
@ISA         = qw(Exporter);
@EXPORT      = ();
@EXPORT_OK   = qw(feedstack);
%EXPORT_TAGS = ( DEFAULT => [qw(&feedstack)] );

sub feedstack {
    my ( $dbh, $file, $ocr ) = @_;
    my $FUSER  = "root";
    my $FGROUP = "FriendlyStack";
    my $uid    = getpwnam($FUSER);
    my $gid    = getgrnam($FGROUP);
    my $basepath;
    my %basepath = (
        Document => "/home/pstack/Documents",
        Picture  => "/home/pstack/Multimedia",
        Video    => "/home/pstack/Multimedia",
    );

    #my $PDFTOTEXT = `which pdftotext`; chomp $PDFTOTEXT;
    my $PDFTOTEXT = "/usr/bin/pdftotext";
    my $CONVERT   = `which convert`;
    chomp $CONVERT;

    use Image::ExifTool;
    use Lingua::Identify qw(:language_identification);
    use Encode qw(encode decode);
    use POSIX ":sys_wait_h";
    Lingua::Identify::deactivate_all_languages();
    Lingua::Identify::activate_language('en');
    Lingua::Identify::activate_language('de');
    Lingua::Identify::activate_language('fr');
    %filters = ( "doc", "writer_pdf_Export", "docx", "writer_pdf_Export", "xls", "calc_pdf_Export", "xlsx", "calc_pdf_Export", "ppt", "impress_pdf_Export", "pptx", "impress_pdf_Export", "vsd", "draw_pdf_Export", "vsdx", "draw_pdf_Export", "vdx", "draw_pdf_Export" );
    %Months = ( "Januar", 1, "Februar", 2, "März", 3, "April", 4, "Mai", 5, "Juni", 6, "Juli", 7, "August", 8, "September", 9, "Oktober", 10, "November", 11, "Dezember", 12, "January", 1, "February", 2, "March", 3, "April", 4, "May", 5, "June", 6, "July", 7, "August", 8, "September", 9, "October", 10, "November", 11, "December", 12, "JAN", 1, "FEB", 2, "MAR", 3, "APR", 4, "MAY", 5, "JUN", 6, "JUL", 7, "AUG", 8, "SEP", 9, "OCT", 10, "NOV", 11, "DEC", 12 );
    %birthdays = ( );
    my $media;
    use File::Path qw(make_path remove_tree);

    #use DBI;
    #$dbh = DBI->connect('dbi:mysql:dbname=pStack;host=localhost','test','test',{AutoCommit=>1,RaiseError=>1,PrintError=>0});
    $sth = $dbh->prepare("SET NAMES 'utf8'");
    $rv  = $sth->execute;
    $sth = $dbh->prepare("SET low_priority_updates=1");
    $rv  = $sth->execute;
    use File::Basename;
    use File::Find;
    use File::stat;
    $time     = time();
    $exifTool = new Image::ExifTool;

    #if ((-f $file) && ($file =~ /\.(pdf|jpg|jpeg|r[a]?w2|avi|mp4|mts|mov)$/i) && !(-f "/home/videos/".basename($file).".mp4"))
    if ( ( -f $file ) && ( $file =~ /\.(pdf|jpg|jpeg|r[a]?w2|png|bmp|avi|mp4|mts|mov|doc[x]?)$|xls[x]?|ppt[x]?|vsd[x]?|vdx/i ) ) {
        $path = $file;
        $quotedpath = $dbh->quote( decode( 'utf-8', $path ) );

        #$quotedpath = $dbh->quote( $path );
        $language = "N/A";
        if ( $file =~ /\.(pdf|jpg|jpeg|r[a]?w2|png|bmp|avi|mp4|mts|mov|doc|doc[x]?|xls[x]?|ppt[x]?|vsd[x]?|vdx)$/i ) { $extension = $1 }
        @mdate = CORE::localtime( stat($file)->mtime );
        $ts    = sprintf( "'%4d-%02d-%02d %02d:%02d:%02d'", $mdate[5] + 1900, $mdate[4] + 1, $mdate[3], $mdate[2], $mdate[1], $mdate[0] );
        $sth   = $dbh->prepare("select ID from Documents where path=$quotedpath and timestamp(ts)=timestamp($ts)");
        $rv    = $sth->execute;
        if ( $sth->rows() == 0 ) {

            #         print "$path\n";
            if ( $extension =~ /pdf|doc[x]?|xls[x]?|ppt[x]?|vsd[x]?|vdx/i ) {
                $media = "Document";

                #$basepath = "/home/pstack/Documents";
                if ( $extension =~ /doc[x]?|xls[x]?|ppt[x]?|vsd[x]?|vdx/i ) {

                    #using $$ to create a subdirectory with the PID in the temp folder in order to avoid race conditions with parallel processing
                    $scommand = "/usr/bin/soffice --headless --convert-to pdf:\"" . $filters{ lc($extension) } . "\" --outdir /tmp/$$ \"$path\"";
                    ($filename) = basename($path) =~ /(.+)\..+$/;
                    make_path("/tmp/$$");

                    unless ( $pid = fork ) {

                        #This is required to prevent the DB handle from being destroyed upon termination of the child process
                        $dbh->{InactiveDestroy} = true;
                        $sth->{InactiveDestroy} = true;

                        #this is a work around for avoiding LibeOffice dying wirhout producing an output. This seems to happen when multiple instances run in parallel.
                        #Presumably this is fixed in LibreOffice Version 5.3.0.
                        my $i = 0;
                        do {
                            system($scommand);
                            ++$i;
                            $rc = $? >> 8;
                        } until ( $rc == 0 || $i > 3 );
                        exit(0);
                    }
                    $grace = 0;
                    while ( waitpid( $pid, WNOHANG ) == 0 ) {
                        sleep(1);
                        ++$grace;
                        if ( $grace > 600 ) {
                            kill( 9, $pid );
                            `pkill -9 -f \/tmp\/$$`;
                            `cp /home/pstack/bin/NoPreview.pdf "\/tmp\/$$\/$filename.pdf"`;
                        }
                    }

                    #$scommand = "$PDFTOTEXT -layout -q -nopgbrk -enc UTF-8 " . "\"/tmp/$filename.pdf\" -";

                    $scommand = "/usr/bin/java -jar /home/pstack/bin/pdfbox-app-2.0.8.jar ExtractText \"/tmp/$$/$filename.pdf\" -ignoreBeads -sort -console -encoding UTF-8 2>/dev/null";

                    #$scommand="/usr/bin/java -jar /home/pstack/bin/pdfbox-app-2.0.2.jar ExtractText \"/tmp/$filename.pdf\" -console -encoding UTF-8 -sort";
                    #$scommand = "/usr/bin/gs -dQUIET -dBATCH -dNOPAUSE -sDEVICE=txtwrite -sOutputFile=- \"/tmp/$$/$filename.pdf\"";
                }
                else {
                    #$scommand = "$PDFTOTEXT -layout -q -nopgbrk -enc UTF-8 " . "\"" . $path . "\" -";

                    $scommand = "/usr/bin/java -jar /home/pstack/bin/pdfbox-app-2.0.8.jar ExtractText \"$path\" -ignoreBeads -sort -console -encoding UTF-8 2>/dev/null";

                    #$scommand="/usr/bin/java -jar /home/pstack/bin/pdfbox-app-2.0.2.jar ExtractText \"$path\" -console -encoding UTF-8 -sort";
                    #$scommand = "/usr/bin/gs -dQUIET -dBATCH -dNOPAUSE -sDEVICE=txtwrite -sOutputFile=- \"$path\"";
                }

                #$scommand="/usr/bin/java -jar /home/pstack/bin/pdfbox-app-1.8.9.jar ExtractText \"$path\" -console -encoding UTF-8 -force";
                $content = decode( 'utf-8', `$scommand` );
                ( $language, $probability ) = langof( { method => { smallwords => 0.5, ngrams3 => 1.5 } }, $content );
                $ContentDate = NULL;
                if ( ( $language =~ /en/i ) && ( $probability > 0.2 ) ) {

                    #print "$probability \t $path\n";
                    #while ($content =~ /(January|February|March|April|May|June|July|August|September|October|November|December|Januar|Februar|März|April|Mai|Juni|Juli|August|September|Oktober|November|Dezember|JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)[\.,]*\s*(\d{1,2})[\.,]*\s*(\d{4}|\d{2})(?!\d)|(?<!\d)(\d{1,2})[\-\/\.](\d{1,2})[\-\/\.](\d{4}|\d{2})(?!\d)/gs)
                    while ( $content =~ /(?<!\d\s)(January|February|March|April|May|June|July|August|September|October|November|December|JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)[\.,]*\s*(\d{1,2})[\.,]*\s+(\d{4}|\d{2})(?!\d)|(?<!\d)(\d{1,2})[\-\/\.](\d{1,2})[\-\/\.](\d{4}|\d{2})(?!\d)/gs ) {
                        $yearpadding = 0;

                        #print "$&\n";
                        if ( exists( $Months{$1} ) ) {
                            if ( $3 < 1900 ) {
                                if   ( $3 < 20 ) { $yearpadding = 2000 }
                                else             { $yearpadding = 1900 }
                            }
                            $ContentDate = sprintf( "'%4d-%02d-%02d %02d:%02d:%02d'", $3 + $yearpadding, $Months{$1}, $2, 0, 0, 0 );
                        }
                        else {
                            if ( $4 > 12 ) {
                                if ( $6 < 1900 ) {
                                    if   ( $6 < 20 ) { $yearpadding = 2000 }
                                    else             { $yearpadding = 1900 }
                                }
                                $ContentDate = sprintf( "'%4d-%02d-%02d %02d:%02d:%02d'", $6 + $yearpadding, $5, $4, 0, 0, 0 );
                            }
                            elsif ( $5 > 12 ) {
                                if ( $6 < 1900 ) {
                                    if   ( $6 < 20 ) { $yearpadding = 2000 }
                                    else             { $yearpadding = 1900 }
                                }
                                $ContentDate = sprintf( "'%4d-%02d-%02d %02d:%02d:%02d'", $6 + $yearpadding, $4, $5, 0, 0, 0 );
                            }
                            else {
                                if ( $6 < 1900 ) {
                                    if   ( $6 < 20 ) { $yearpadding = 2000 }
                                    else             { $yearpadding = 1900 }
                                }
                                $ContentDate = sprintf( "'%4d-%02d-%02d %02d:%02d:%02d'", $6 + $yearpadding, $4, $5, 0, 0, 0 );
                            }
                        }

                        #if ((!exists($birthdays{$ContentDate})) && (exists($Months{$1}) || (12>=$5 && $5>=1)) && ((31>=$2 && $2>=1) || (31>=$4 && $4>=1))){last} else {$ContentDate="NULL"}
                        if   ( ( !exists( $birthdays{$ContentDate} ) ) && ( ( exists( $Months{$1} ) && ( 31 >= $2 && $2 >= 1 ) ) || ( ( 31 >= $4 && $4 >= 1 ) && ( 12 >= $5 && $5 >= 1 ) ) || ( ( 31 >= $5 && $5 >= 1 ) && ( 12 >= $4 && $4 >= 1 ) ) ) ) { last }
                        else                                                                                                                                                                                                                             { $ContentDate = "NULL" }
                    }
                    if ( $ContentDate eq "NULL" ) {

                        #while ($content =~ /(?<!\d\.\s)(Januar|Februar|März|April|Mai|Juni|Juli|August|September|Oktober|November|Dezember)\s+(\d{2,4})/gs)
                        #{
                        #if (exists($Months{$1})){$ContentDate=sprintf ("'%4d-%02d-%02d %02d:%02d:%02d'",$2,$Months{$1},1,0,0,0)}
                        #print "$& \t $ContentDate \t $path\n";
                        #if ((!exists($birthdays{$ContentDate})) && (exists($Months{$1})) ){last} else {$ContentDate="NULL"}
                        #}
                    }

                    #print "$ContentDate\n";
                }
                if ( ( ( $ContentDate eq "NULL" ) && ( $language =~ /en/i ) ) || !( $language =~ /en/i ) ) {
                    while ( $content =~ /(\d{1,2})\.\s*(Januar|Februar|März|April|Mai|Juni|Juli|August|September|Oktober|November|Dezember|January|February|March|April|May|June|July|Augut|September|October|November|December)\s+(\d{4}|\d{2})(?!\d)|(?<!\d)(\d{1,2})[\/\.](\d{1,2})[\/\.](\d{4}|\d{2})(?!\d)/gs ) {
                        $yearpadding = 0;
                        if ( exists( $Months{$2} ) ) {
                            if ( $3 < 1900 ) {
                                if   ( $3 < 20 ) { $yearpadding = 2000 }
                                else             { $yearpadding = 1900 }
                            }
                            $ContentDate = sprintf( "'%4d-%02d-%02d %02d:%02d:%02d'", $3 + $yearpadding, $Months{$2}, $1, 0, 0, 0 );
                        }
                        else {
                            if ( $6 < 1900 ) {
                                if   ( $6 < 20 ) { $yearpadding = 2000 }
                                else             { $yearpadding = 1900 }
                            }
                            $ContentDate = sprintf( "'%4d-%02d-%02d %02d:%02d:%02d'", $6 + $yearpadding, $5, $4, 0, 0, 0 );
                        }
                        if   ( ( !exists( $birthdays{$ContentDate} ) ) && ( exists( $Months{$2} ) || ( 12 >= $5 && $5 >= 1 ) ) && ( ( 31 >= $1 && $1 >= 1 ) || ( 31 >= $4 && $4 >= 1 ) ) ) { last }
                        else                                                                                                                                                               { $ContentDate = "NULL" }
                    }
                    if ( $ContentDate eq "NULL" ) {
                        while ( $content =~ /(?<!\d[\.-]\s)(Januar|Februar|März|April|Mai|Juni|Juli|August|September|Oktober|November|Dezember)\s+(\d{4}|\d{2})\D+/gs ) {
                            if ( exists( $Months{$1} ) ) {
                                if ( $2 < 1900 ) {
                                    if   ( $2 < 20 ) { $yearpadding = 2000 }
                                    else             { $yearpadding = 1900 }
                                }
                                $ContentDate = sprintf( "'%4d-%02d-%02d %02d:%02d:%02d'", $2 + $yearpadding, $Months{$1}, 1, 0, 0, 0 );
                            }

                            #print "$& \t $ContentDate \t $path\n";
                            if   ( ( !exists( $birthdays{$ContentDate} ) ) && ( exists( $Months{$1} ) ) ) { last }
                            else                                                                          { $ContentDate = "NULL" }
                        }
                    }
                }
                if ( $ContentDate eq "NULL" ) { $ContentDate = $ts }
            }
            elsif ( $extension =~ /jp[e]?g|png|bmp/i ) {
                $media = "Picture";

                #$basepath = "/home/pstack/Multimedia";

                #if ($ocr==1) {$scommand="/usr/local/bin/abbyyocr11 -ido --detectInvertedImage --detectTextOnPictures --enableAggressiveTextExtraction -recc -rldm yes -rl German English French --outputToStdout -if "."\"".$path."\""; $content=decode('utf-8',`$scommand`); $language=langof($content);} else {$content="NULL"}
                #if ( $ocr == 1 ) { $scommand = "/usr/local/bin/abbyyocr11 -ido --detectTextOnPictures --enableAggressiveTextExtraction -recc -rldm yes -rl German English French --outputToStdout -if " . "\"" . $path . "\""; $content = decode( 'utf-8', `$scommand` ); $language = langof($content); }
                #if ( $ocr == 1 ) { $scommand = "/usr/bin/tesseract " . "\"" . $path . "\" stdout"; $content = decode( 'utf-8', `$scommand` ); $language = langof($content); }
                if ( $ocr == 1 ) { $scommand = "/home/pstack/bin/FriendlyStackOCR " . "\"" . $path . "\" -"; $content = decode( 'utf-8', `$scommand` ); $language = langof($content); print "$content\n"; }
                else             { $content = "NULL" }

                #if ($ocr==1) {$scommand="/usr/local/bin/abbyyocr11 -ido --detectTextOnPictures --enableAggressiveTextExtraction -recc -rldm yes -rl German English French --outputToStdout -if "."\"".$path."\""; $content=`$scommand`; $language=langof($content);} else {$content="NULL"}
                $info = $exifTool->ImageInfo($path);
                if    ( $$info{'CreateDate'} =~ /(\d{4})\:(\d{2})\:(\d{2})\s(\d{2})\:(\d{2})\:(\d{2})/ )       { $ContentDate = sprintf( "'%4d-%02d-%02d %02d:%02d:%02d'", $1, $2, $3, $4, $5, $6 ); }
                elsif ( $$info{'DateTimeOriginal'} =~ /(\d{4})\:(\d{2})\:(\d{2})\s(\d{2})\:(\d{2})\:(\d{2})/ ) { $ContentDate = sprintf( "'%4d-%02d-%02d %02d:%02d:%02d'", $1, $2, $3, $4, $5, $6 ); }
                elsif ( basename($path) =~ /^(\d{4})\-(\d{2})\-(\d{2})\s(\d{2})\-(\d{2})\-(\d{2}).*/ )         { $ContentDate = sprintf( "'%4d-%02d-%02d %02d:%02d:%02d'", $1, $2, $3, $4, $5, $6 ); }
                else                                                                                           { $ContentDate = $ts }

            }
            elsif ( $extension =~ /r[a]?w2/i ) {
                $media = "Picture";

                #$basepath = "/home/pstack/Multimedia";
                $info = $exifTool->ImageInfo($path);
                if ( $$info{'DateTimeOriginal'} =~ /(\d{4})\:(\d{2})\:(\d{2})\s(\d{2})\:(\d{2})\:(\d{2})/ ) { $ContentDate = sprintf( "'%4d-%02d-%02d %02d:%02d:%02d'", $1, $2, $3, $4, $5, $6 ); }
                else                                                                                        { $ContentDate = $ts }
            }
            elsif ( $extension =~ /(avi|mp4|mts|mov)/i ) {
                $media = "Video";

                #$basepath = "/home/pstack/Multimedia";
                $content = "NULL";
                $info    = $exifTool->ImageInfo($path);
                if    ( $$info{'CreateDate'} =~ /(\d{4})\:(\d{2})\:(\d{2})\s(\d{2})\:(\d{2})\:(\d{2})/ )       { $ContentDate = sprintf( "'%4d-%02d-%02d %02d:%02d:%02d'", $1, $2, $3, $4, $5, $6 ); }
                elsif ( $$info{'DateTimeOriginal'} =~ /(\d{4})\:(\d{2})\:(\d{2})\s(\d{2})\:(\d{2})\:(\d{2})/ ) { $ContentDate = sprintf( "'%4d-%02d-%02d %02d:%02d:%02d'", $1, $2, $3, $4, $5, $6 ); }
                elsif ( basename($path) =~ /^(\d{4})\-(\d{2})\-(\d{2})\s(\d{2})\-(\d{2})\-(\d{2}).*/ )         { $ContentDate = sprintf( "'%4d-%02d-%02d %02d:%02d:%02d'", $1, $2, $3, $4, $5, $6 ); }
                else                                                                                           { $ContentDate = $ts }

                #$moviepath = "/home/pstack/Transcoded_Videos/" . basename($path) . ".mp4";

                #if ( !-f $moviepath ) {

                #$scommand = "/usr/bin/avconv -threads 4 -v 0 -i \"" . $path . "\" -acodec aac -strict experimental -ab 128k -ar 44100 -y -s qvga -f mp4 \"$moviepath\"";
                #$scommand = "/usr/bin/avconv -threads 2 -v 0 -i \"" . $path . "\" -acodec aac -strict experimental -vcodec libx264 -pix_fmt yuv420p -crf 19 -y -s qvga -f mp4 \"$moviepath\"";
                #$scommand = "/usr/bin/avconv -v 0 -i \"" . $path . "\" -acodec aac -strict experimental -vcodec libx264 -pix_fmt yuv420p -crf 19 -y -s qvga -f mp4 \"$moviepath\"";
                #system("$scommand");
                #chown( $uid, $gid, "$moviepath" );
                #chmod( 0660, "$moviepath" );
                #}
                $quotedpath = $dbh->quote($path);

            }
            $sth           = $dbh->prepare("delete LOW_PRIORITY from `Documents` where path=$quotedpath");
            $rv            = $sth->execute;
            $quotedcontent = $dbh->quote($content);
            if ( $ContentDate =~ /^'1900.*|^'   0.*/ ) { $ContentDate = $ts; }

            #Remove the basepath prefix to avoid finding stuff containing related words (such as home...)
            $relpath = $quotedpath;
            $relpath =~ s/\Q$basepath{$media}//;
            $sth = $dbh->prepare("delete from Documents where path = $quotedpath");
            $rv  = $sth->execute;
            $sth = $dbh->prepare("insert LOW_PRIORITY into Documents (path,relpath,content,ts,checked,thumb,page,ContentDate,Language,Media) values ($quotedpath,$relpath,$quotedcontent,$ts,$time,NULL,NULL,$ContentDate,'$language','$media')");
            $rv  = $sth->execute;
            $sth = $dbh->prepare("SELECT LAST_INSERT_ID() as `ID` FROM `Documents`");
            $rv  = $sth->execute;
            $row = $sth->fetchrow_hashref();
            if ( $extension =~ /doc[x]?|xls[x]?|ppt[x]?|vsd[x]?|vdx/i ) {
                $scommand = "$CONVERT -density 100 \"/tmp/$$/$filename.pdf\"[0] -resize 720 -flatten -background white -type Grayscale -depth 4 -define png:color-type=0 -define -dither none /home/pstack/Previews/$row->{'ID'}.png 2>/dev/null";

                #$scommand = "gs -dNOPAUSE -dBATCH -sDEVICE=pnggray -r75 -dFirstPage=1 -dLastPage=1 -sOutputFile=\"/home/pstack/Previews/$row->{'ID'}.png\" \"/tmp/$filename.pdf\"";
                system("$scommand");

                unlink("/tmp/$$/$filename.pdf");
		#rmdir("/tmp/$$");
                remove_tree("/tmp/$$");
            }
            elsif ( $extension =~ /pdf/i ) {
                $scommand = "$CONVERT -density 100 \"$path\"[0] -resize 720 -flatten -background white -type Grayscale -depth 4 -define png:color-type=0 -define -dither none /home/pstack/Previews/$row->{'ID'}.png 2>/dev/null";

                #$scommand = "gs -dNOPAUSE -dBATCH -sDEVICE=pnggray -r75 -dFirstPage=1 -dLastPage=1 -sOutputFile=\"/home/pstack/Previews/$row->{'ID'}.png\" \"$path\"";
                #$scommand = "$CONVERT \"$path\"[0] -density 100 -resize 720 -flatten -background white -type Grayscale -page +4+4 -alpha set \\( +clone -background black -shadow 60x4+4+4 \\) +swap -background none -mosaic /home/pstack/Previews/$row->{'ID'}.png";
                system("$scommand");
            }
            elsif ( $extension =~ /jp[e]?g|png|bmp/i ) {
                $scommand = "$CONVERT \"$path\" -auto-orient -resize 180 -bordercolor snow -background black +polaroid /home/pstack/Previews/$row->{'ID'}.png 2>/dev/null";
                system("$scommand");

            }
            elsif ( $extension =~ /r[a]?w2/i ) {
                $scommand = "$CONVERT rgb:\"$path\" -interlace plane -size 320 -flatten -background white -depth 8 -colors 256 -dither none /home/pstack/Previews/$row->{'ID'}.png 2>/dev/null";
                system("$scommand");

            }
            elsif ( $extension =~ /(avi|mp4|mts|mov)/i ) {
                $moviepath = "/home/pstack/Previews/$row->{'ID'}.mp4";

                #$scommand = "nice --adjustment=10 /usr/bin/avconv -threads 0 -v 0 -i \"" . $path . "\" -threads 0 -acodec aac -strict -2 -vcodec libx264 -pix_fmt yuv420p -crf 19 -y -filter:v scale=\"trunc(oh*a/2)*2:240\" -f mp4 \"$moviepath\"";
                #$scommand = "nice --adjustment=10 /usr/bin/avconv -threads 0 -v 0 -i \"" . $path . "\" -threads 0 -acodec aac -strict -2 -vcodec libx264 -pix_fmt yuv420p -crf 19 -y -filter:v scale=\"trunc(oh*a/2)*2:320\" -f mp4 \"$moviepath\" -filter:v scale=\"trunc(oh*a/2)*2:720\" -f mp4 -strict -2 \"$moviepath.mp4\"";
                #$scommand = "nice --adjustment=10 /usr/bin/ffmpeg -threads 0 -v 0 -i \"" . $path . "\" -threads 0 -acodec aac -strict -2 -vcodec libx264 -pix_fmt yuv420p -y -filter:v scale=\"trunc(oh*a/2)*2:240\" -movflags +faststart -f mp4 \"$moviepath\" -threads 0 -filter:v scale=\"trunc(oh*a/2)*2:720\" -pix_fmt yuv420p -y -strict -2 -movflags +faststart -f mp4 \"$moviepath.mp4\"";
		#$scommand = "nice --adjustment=30 /usr/bin/ffmpeg -threads 0 -v 0 -i \"" . $path . "\" -threads 0 -acodec aac -strict -2 -vcodec libx264 -pix_fmt yuv420p -y -filter:v scale=\"trunc(oh*a/2)*2:240\" -movflags +faststart -f mp4 \"$moviepath\" -threads 0 -filter:v scale=\"trunc(oh*a/2)*2:720\" -pix_fmt yuv420p -y -strict -2 -movflags +faststart -f mp4 \"$moviepath.mp4\"";
		$scommand = "nice --adjustment=30 ffmpeg -threads 0 -v 0 -i \"" . $path . "\" -threads 0 -acodec aac -strict -2 -vcodec libx264 -pix_fmt yuv420p -y -filter:v scale=\"trunc(oh*a/2)*2:240\" -movflags +faststart -f mp4 \"$moviepath\" -threads 0 -filter:v scale=\"trunc(oh*a/2)*2:720\" -pix_fmt yuv420p -y -strict -2 -movflags +faststart -f mp4 \"$moviepath.mp4\"";
                system("$scommand");
                chown( $uid, $gid, "$moviepath" );
                chmod( 0660, "$moviepath" );

                #$scommand = "/usr/bin/avconv -v 0 -i \"$moviepath\" -vframes 1 -an -ss 0.1 -filter:v scale=\"trunc(oh*a/2)*2:240\" -f image2 /home/pstack/Previews/$row->{'ID'}.png 2>/dev/null";
                #$scommand = "/usr/bin/avconv -v 0 -i \"$moviepath\" -vframes 1 -an -ss 0.1 -filter:v scale=\"trunc(oh*a/2)*2:240\" -f image2 /home/pstack/Previews/$row->{'ID'}.png 2>/dev/null && composite -dissolve 35% -gravity southeast ic_hd_white_24dp_2x.png /home/pstack/Previews/$row->{'ID'}.png /home/pstack/Previews/$row->{'ID'}.png";
                #$scommand = "/usr/bin/avconv -v 0 -i \"$moviepath\" -vframes 1 -an -ss 0.1 -filter:v scale=\"320:trunc(ow/a/2)*2\" -f image2 /home/pstack/Previews/$row->{'ID'}.png 2>/dev/null && composite -dissolve 35% -gravity southeast ic_hd_white_24dp_2x.png /home/pstack/Previews/$row->{'ID'}.png /home/pstack/Previews/$row->{'ID'}.png";
		#$scommand = "/usr/bin/avconv -v 0 -i \"$path\" -vframes 1 -an -ss 0.1 -filter:v scale=\"-2:320\" -f image2 /home/pstack/Previews/$row->{'ID'}.png 2>/dev/null && composite -dissolve 35% -gravity southeast ic_hd_white_24dp_2x.png /home/pstack/Previews/$row->{'ID'}.png /home/pstack/Previews/$row->{'ID'}.png && composite -dissolve 45% -gravity center ic_play_circle_outline_white_24dp_2x.png /home/pstack/Previews/$row->{'ID'}.png /home/pstack/Previews/$row->{'ID'}.png";
		$scommand = "ffmpeg -v 0 -i \"$path\" -vframes 1 -an -ss 0.1 -filter:v scale=\"-1:320\" -f image2 /home/pstack/Previews/$row->{'ID'}.png 2>/dev/null && composite -dissolve 35% -gravity southeast ic_hd_white_24dp_2x.png /home/pstack/Previews/$row->{'ID'}.png /home/pstack/Previews/$row->{'ID'}.png && composite -dissolve 45% -gravity center ic_play_circle_outline_white_24dp_2x.png /home/pstack/Previews/$row->{'ID'}.png /home/pstack/Previews/$row->{'ID'}.png";
                system("$scommand");
            }
            chown( $uid, $gid, "/home/pstack/Previews/$row->{'ID'}.png" );
            chmod( 0660, "/home/pstack/Previews/$row->{'ID'}.png" );
        }
        else {
            $sth = $dbh->prepare("UPDATE LOW_PRIORITY `Documents` SET `checked`=$time WHERE path=$quotedpath and timestamp(ts)=timestamp($ts)");
            $rv  = $sth->execute;
        }
    }

    $sth->finish;

    #$dbh->disconnect();
}
