#!/bin/bash
#this scripts to encode video: from a video file to a .index.m3u8 file for Http Live Streaming!
source 'bin/encoder/lib.sh'
NEWFILE=$1
log "$LEVEL_INFO" "new video detected: ${NEWFILE}"
DIR=$(dirname ${NEWFILE})
rm -f ${DIR}/*.m3u8 ${DIR}/*.ts ${DIR}/lock ${DIR}/error
touch ${DIR}/lock
log "$LEVEL_INFO" "encoding ${NEWFILE}..."
ffmpeg -i ${NEWFILE}  -hls_list_size 0 -f hls ${DIR}/index.m3u8 2> /dev/null
if [ $? -eq 0 ]; then
    log "$LEVEL_INFO" "${NEWFILE} encoded!"
else
    log "$LEVEL_ERROR" "${NEWFILE} : encoding error"
    touch ${DIR}/error
fi
rm -f ${DIR}/lock