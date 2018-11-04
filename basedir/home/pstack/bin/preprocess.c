/*====================================================================*
 -  Copyright (C) 2001 Leptonica.  All rights reserved.
 -
 -  Redistribution and use in source and binary forms, with or without
 -  modification, are permitted provided that the following conditions
 -  are met:
 -  1. Redistributions of source code must retain the above copyright
 -     notice, this list of conditions and the following disclaimer.
 -  2. Redistributions in binary form must reproduce the above
 -     copyright notice, this list of conditions and the following
 -     disclaimer in the documentation and/or other materials
 -     provided with the distribution.
 -
 -  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 -  ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 -  LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 -  A PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL ANY
 -  CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 -  EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 -  PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 -  PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 -  OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 -  NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 -  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *====================================================================*/

/*
 * flipdetect_reg.c
 *
 *   Tests 90 degree orientation of text and whether the text is
 *   mirror reversed.  Compares the rasterop with dwa implementations
 *   for speed.  Shows the typical 'confidence' outputs from the
 *   functions in flipdetect.c.
 */

#include "allheaders.h"

static void printStarredMessage(const char *msg);

int main(int    argc,
         char **argv)
{
char        *filein;
l_int32      i, orient, d, w;
l_float32    upconf1, upconf2, leftconf1, leftconf2, conf1, conf2;
PIX         *pixs, *pixt2, *pixt3, *pixg, *pixg2 ;
static char  mainName[] = "preprocess";

    if (argc != 2)
        return ERROR_INT(" Syntax: preprocess filein", mainName, 1);

    filein = argv[1];

    if ((pixs = pixRead(filein)) == NULL)
        return ERROR_INT("pixt1 not made", mainName, 1);
    pixGetDimensions(pixs, &w, NULL, &d);


    if (d == 32)
        pixg = pixConvertRGBToGray(pixs, 0.2, 0.7, 0.1);
    else
        pixg = pixConvertTo8(pixs, 0);
    pixg2 = pixContrastNorm(NULL, pixg, 20, 20, 130, 2, 2);
    pixSauvolaBinarizeTiled(pixg2, 25, 0.40, 1, 1, NULL, &pixt2);
    pixDestroy(&pixg);
    pixDestroy(&pixg2);




        pixOrientDetectDwa(pixt2, &upconf2, &leftconf2, 0, 0);
        makeOrientDecision(upconf2, leftconf2, 0, 0, &orient, 1);
/* The return value of orient is interpreted thus: DEFAULT_MIN_UP_DOWN_CONF
 *            L_TEXT_ORIENT_UNKNOWN:  not enough evidence to determine
 *            L_TEXT_ORIENT_UP:       text rightside-up
 *            L_TEXT_ORIENT_LEFT:     landscape, text up facing left
 *            L_TEXT_ORIENT_DOWN:     text upside-down
 *            L_TEXT_ORIENT_RIGHT:    landscape, text up facing right
 */

/*As confident level seems to be the same as for pixOrientDetectDwa this does not help ...

        if (orient == L_TEXT_ORIENT_UNKNOWN)
        {
            pixOrientDetect(pixt2, &upconf2, &leftconf2, 0, 0);
            makeOrientDecision(upconf2, leftconf2, 0, 0, &orient, 1);
        }
*/
    if(orient == L_TEXT_ORIENT_DOWN)
		pixt3 = pixRotateOrth(pixs,2);
	else if(orient == L_TEXT_ORIENT_LEFT)
		pixt3 = pixRotateOrth(pixs,1);
	else if(orient == L_TEXT_ORIENT_RIGHT)
		pixt3 = pixRotateOrth(pixs,3);
	else if(orient == L_TEXT_ORIENT_UNKNOWN)
		printf("%s", "Could not determine text orientation\n");
        else if(orient == L_TEXT_ORIENT_UP)
		pixt3 = pixRotateOrth(pixs,0);
            
	if (!(orient == L_TEXT_ORIENT_UNKNOWN)) 
{
pixWrite(filein, pixDeskew(pixt3,0), IFF_TIFF);
pixDestroy(&pixt2);
pixDestroy(&pixt3);
} else pixWrite(filein, pixDeskew(pixs,0), IFF_TIFF);

    pixDestroy(&pixs);
    return 0;
}
