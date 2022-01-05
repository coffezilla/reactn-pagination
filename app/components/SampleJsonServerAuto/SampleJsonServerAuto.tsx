import { useState } from 'react';
import PaginationJson from '../PaginationJson';

interface IData {
	data: string[];
}

const SampleJsonServerAuto = () => {
	const [paginationCurrentData, setPaginationCurrentData] = useState<IData['data']>([]);
	return (
		<>
			<h1>Sample Json Server - AUTO LOAD</h1>
			<h2>Result:</h2>
			<ul className="list-box-local">
				{paginationCurrentData.map((result: any) => {
					return <li key={result.id}>{`${result.id} - ${result.title}`}</li>;
				})}
			</ul>
			<PaginationJson
				data="https://jsonplaceholder.typicode.com/todos"
				scrollDomReference=".list-box-local"
				setData={setPaginationCurrentData}
				autoLoad
				perPage={30}
			/>
		</>
	);
};

export default SampleJsonServerAuto;
