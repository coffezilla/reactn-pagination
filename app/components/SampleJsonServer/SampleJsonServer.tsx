import { useState } from 'react';
import PaginationJson from '../PaginationJson';

interface IData {
	data: string[];
}

const SampleJsonServer = () => {
	const [paginationCurrentData, setPaginationCurrentData] = useState<IData['data']>([]);
	return (
		<>
			<h1>Sample Json Server</h1>
			<h2>Result:</h2>
			<ul className="list-box-local">
				{paginationCurrentData.map((result: any) => {
					return <li key={result.id}>{`${result.id} - ${result.title}`}</li>;
				})}
			</ul>
			<PaginationJson
				data="https://jsonplaceholder.typicode.com/todos"
				setData={setPaginationCurrentData}
				perPage={20}
			/>
		</>
	);
};

export default SampleJsonServer;
