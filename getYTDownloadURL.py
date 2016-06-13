from pytube import YouTube
import sys, getopt

def main(argv):
	# Set YouTube URL from a given ID from the command line
	yt = YouTube("http://youtube.com/watch?v="+argv[0])
	vid = yt.get('mp4','360p') # Set video to download as an mp4 format at 360p quality
	print(vid.url) # Print the download URL

if __name__ == "__main__":
	main(sys.argv[1:])