from pytube import YouTube
import sys, getopt

def main(argv):
	yt = YouTube("http://youtube.com/watch?v="+argv[0])
	vid = yt.get('mp4','360p')
	print(vid.url)

if __name__ == "__main__":
	main(sys.argv[1:])