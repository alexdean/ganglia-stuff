require 'socket'               # Get sockets from stdlib

xml_dir = File.expand_path( File.dirname( __FILE__ )+'/../xml' )

server = TCPServer.open(8699)
loop {
  client = server.accept
  request = client.recv(1024).strip
  
  filename = xml_dir+'/'
  filename += case request
  when '/?filter=summary'
    "meta.xml"
  when '/cluster1'
    "cluster.xml"
  when '/'
    "index_array.xml"
  when '/cluster1?filter=summary'  
    "cluster-summary.xml"
  when '/cluster1/cluster1-host1'
    "node.xml"
  end
  
  puts "#{Time.now}: #{request} -> #{filename}"
  response = File.exists?( filename.to_s ) ? File.read( filename ) : ""
  puts response
  client.puts( response )
  client.close
}

